<?php
namespace App\Services;

use App\Models\Option;
use App\Models\Product;
use App\Models\Service;
use App\Models\Transaction;
use App\Services\Payment\HandlesSearch;
use App\Services\Payment\SearchResult;
use App\Services\Payment\Status;
use App\Services\Payment\TransactionResult;
use App\Services\Payment\TransactionServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\Client\PendingRequest;
use Log;
use Cache;
use Http;
use Exception;
use LogicException;
use Ramsey\Uuid\Uuid;
use Throwable;

class FujisatService implements TransactionServiceInterface, HandlesSearch
{
    const FUJISAT_TOKEN_KEY = "FUJISAT_TOKEN";
    public function __construct(
        protected string $baseUrl,
        protected string $username,
        protected string $password,
    ) {
    }

    protected function makeRequest(?string $token = null): PendingRequest
    {
        $headers = [
            "Content-Type" => "application/json",
            "Accept" => "application/json",
        ];

        if (!is_null($token)) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        return Http::withOptions([
            "base_uri" => $this->baseUrl,
            "headers" => $headers
        ]);
    }

    public function initiate(Transaction $transaction): TransactionResult
    {
        $token = $this->getToken();
        if ($token == null) {
            return new TransactionResult(Status::ERROR, providerError: "failed to authenticate and generate token");
        }
        $numAbo = $this->getNumAbo($transaction, $token);
        if ($numAbo instanceof Exception) {
            return new TransactionResult(Status::ERROR, providerError: $numAbo->getMessage());
        }
        $res = $this->makeRequest($token)->post("/business/reabonnement", [
            "numabo" => $numAbo,
            "numdecabo" => $transaction->destination,
            "numcontrat" => "1",
            "offreCode" => $transaction->product->provider_id_1,
            "duree" => 1,
            "amount" => $transaction->amount,
            "telephone" => "",
            "option" => $transaction->servicePayment->options->pluck('code')
        ]);
        $data = $res->json();
        Log::info("[FUJISAT] response received", [
            "tx" => $transaction,
            "response" => $res->body(),
            "status_code" => $res->status(),
        ]);
        if (!$data) {
            Log::warning($message = "[FUJISAT] invalid body received while doing payout", [
                "tx" => $transaction,
                "response" => $res->body(),
                "status_code" => $res->status(),
            ]);
            return new TransactionResult(Status::ERROR, providerError: $message);
        }
        if (!array_key_exists("status", $data)) {
            Log::warning($message = "[FUJISAT] missing status in payout response", [
                "tx" => $transaction,
                "response" => $res->body(),
                "status_code" => $res->status(),
            ]);
            return new TransactionResult(Status::ERROR, providerError: $message);
        }

        $status = $data['status'];
        $code = $data['code'];

        if ($status != 200 || $code != 0) {
            Log::info("[FUJISAT] invalid status or code", [
                "tx" => $transaction,
                "response" => $res->body(),
                "status_code" => $res->status(),
            ]);
            $message = $data['data'] ?? $data['message'] ?? "";
            return new TransactionResult(Status::ERROR, providerError: $message);
        }

        return new TransactionResult(Status::SUCCESS);
    }


    public function checkStatus(Transaction $transaction): TransactionResult
    {
        throw new LogicException('status check not supported');
    }

    public function getNumAbo(Transaction $transaction, string $token): string|Throwable
    {
        Log::info("[FUJISAT] loading subscriber number", [
            "uuid" => $transaction->uuid,
            "destination" => $transaction->destination
        ]);
        $res = $this->makeRequest($token)->post('/business/searchSubscriber', $payload = [
            "numabo" => "",
            "numdecabo" => $transaction->destination,
            "emailabo" => "",
            "telabo" => ""
        ]);
        Log::info("[FUJISAT] subscriber response", ["body" => $res->body(), "status" => $res->status()]);
        $data = $res->json();
        if ($res->failed()) {
            Log::warning(
                $message = "[FUJISAT] num abo request failed",
                ["tx" => $transaction, "request" => $payload, 'response' => $res->body(),]
            );
            return new Exception($message);
        }
        if (!$data) {
            Log::warning(
                "[FUJISAT] invalid body received while generating a token",
                ["response" => $res->body(), "tx" => $transaction,]
            );
            throw new Exception("Failed to generate a new token");
        }

        if (!array_key_exists("data", $data)) {
            throw new Exception("invalid response received from client search endpoint");
        }

        $data = $data['data'];

        if (empty($data)) {
            throw new Exception("num abo not found");
        }

        $data = $data[0];

        if (!array_key_exists("numabo", $data)) {
            throw new Exception("invalid response, missing numabo");
        }
        return $data['numabo'];
    }

    /**
     * @return array<array{product:Product,options:array<Option>}>
     */
    public function getProducts(Service $service): array
    {
        $token = $this->getToken();
        $req = $this->makeRequest($token)->get('/operations/offers');
        Log::info("[FUJISAT] auth response", ["body" => $req->body(), "status" => $req->status()]);
        $data = $req->json();
        if ($req->failed()) {
            Log::info("[FUJISAT] options request failed", ["body" => $req->body(), "status" => $req->status()]);
            return [];
        }
        if (!array_key_exists("data", $data) || !is_array($data)) {
            Log::warning(
                "[FUJISAT] invalid response received from get token endpoint",
                ["body" => $req->body(), "status" => $req->status()]
            );
            return [];
        }
        $data = $data['data'];
        $products = [];
        foreach ($data as $offer) {
            $options = [];

            $offerName = $offer['description'];
            $offerPrice = $offer['price'];
            $offerCode = $offer['code'];

            if (str_contains($offerName, 'DSTV')) {
                continue;
            }

            $product = new Product;
            $product->name = $offerName;
            $product->description = $offerName;
            $product->slug = $offerCode;
            $product->provider_id_1 = $offerCode;
            $product->price = $offerPrice;
            $product->enabled = false;
            $product->service()->associate($service);

            foreach ($offer['options'] as $optionData) {
                $option = new Option;
                $option->code = $optionData['codeOption'];
                $option->name = $optionData['descriptionOption'];
                $option->amount = $optionData['priceOption'];

                $options[] = $option;
            }

            $products[] = [
                'product' => $product,
                'options' => $options
            ];
        }

        return $products;
    }

    private function getToken(): ?string
    {
        if (Cache::has(self::FUJISAT_TOKEN_KEY)) {
            return Cache::get(self::FUJISAT_TOKEN_KEY);
        }
        $req = $this->makeRequest()->post('/user/login', [
            "username" => $this->username,
            "password" => $this->password
        ]);
        Log::info("[FUJISAT] auth response", ["body" => $req->body(), "status" => $req->status()]);
        $data = $req->json();
        if ($req->failed()) {
            Log::info("[FUJISAT] token request failed", ["body" => $req->body(), "status" => $req->status()]);
            return null;
        }
        if (!$data) {
            Log::warning(
                "[FUJISAT] invalid body received while generating a token",
                ["body" => $req->body(), "status" => $req->status()]
            );
            return null;
        }
        if (!array_key_exists("data", $data)) {
            Log::warning(
                "[FUJISAT] invalid response received from get token endpoint",
                ["body" => $req->body(), "status" => $req->status()]
            );
            return null;
        }
        if (!array_key_exists("token", $data['data']) && !array_key_exists("tokenExpirationDate", $data['data'])) {
            Log::warning(
                "[FUJISAT] invalid response, missing token or expiry date",
                ["body" => $req->body(), "status" => $req->status()]
            );
            return null;
        }
        $token = $data['data']["token"];
        $tokenExpirationDate = (string) $data['data']["tokenExpirationDate"];
        $lifetime = Carbon::parse($tokenExpirationDate)->diff(now())->s;
        Cache::put(self::FUJISAT_TOKEN_KEY, $token, $lifetime);

        return $token;
    }

    public function searchSubscription(Service $service, string $query): ?SearchResult
    {
        $searchResults = $this->searchSubscriber($service, $query);
        if (empty($searchResults)) {
            return null;
        }
        /** @var SearchResult */
        $subscription = $searchResults[0];
        $token = $this->getToken();
        Log::info("[FUJISAT] sending subscriber search request", [
            "query" => $query,
        ]);

        $res = $this->makeRequest($token)->get('/business/reabonnement', $payload = [
            "device" => $query,
        ]);

        Log::info("[FUJISAT] search response", ["body" => $res->body(), "status" => $res->status()]);
        $data = $res->json();

        if ($res->failed()) {
            Log::warning(
                "[FUJISAT] search request failed",
                ["query" => $query, "request" => $payload, 'response' => $res->body(),]
            );
            return null;
        }

        if (!$data) {
            Log::warning(
                "[FUJISAT] invalid body received, invalid body",
                ["response" => $res->body(),]
            );
            return null;
        }

        if (!array_key_exists("data", $data)) {
            Log::warning(
                "[FUJISAT] invalid body received missing data",
                [
                    "response" => $res->body(),
                    "query" => $query,
                ]
            );
            return null;
        }

        if (!array_key_exists("status", $data)) {
            Log::warning(
                "[FUJISAT] invalid body received missing status",
                [
                    "response" => $res->body(),
                    "query" => $query,
                ]
            );
            return null;
        }

        if (!array_key_exists("code", $data)) {
            Log::warning(
                "[FUJISAT] invalid body received missing code",
                [
                    "response" => $res->body(),
                    "query" => $query,
                ]
            );
            return null;
        }

        $status = $data['status'];
        $code = $data['code'];

        if ($status != 200 || $code != 0) {
            Log::info("[FUJISAT] invalid status or code", [
                "response" => $res->body(),
                "query" => $query,
            ]);
            return null;
        }

        $data = $data['data'];

        $subscriptionCode = $data['offreCodeLC'];

        /** @var ?Product **/
        $product = $service->enabledProducts()->where('provider_id_1', $subscriptionCode)->first();

        if (!$product) {
            Log::info("[FUJISAT] product not found", [
                "response" => $res->body(),
                "query" => $query,
            ]);
            return null;
        }

        return new SearchResult(
            id: $subscription->id,
            billNumber: $subscription->billNumber,
            customerName: $subscription->customerName,
            productId: $product->id,
            description: $subscription->id,
            customerNumber: $subscription->customerNumber
        );

    }

    /**
     * @return array<SearchResult>
     */
    public function searchSubscriber(Service $service, string $query): array
    {
        $token = $this->getToken();
        Log::info("[FUJISAT] sending subscriber search request", [
            "query" => $query,
        ]);

        $res = $this->makeRequest($token)->post('/business/searchSubscriber', $payload = [
            "numabo" => "",
            "numdecabo" => $query,
            "emailabo" => "",
            "telabo" => ""
        ]);

        Log::info("[FUJISAT] search response", ["body" => $res->body(), "status" => $res->status()]);
        $data = $res->json();

        if ($res->failed()) {
            Log::warning(
                "[FUJISAT] search request failed",
                ["query" => $query, "request" => $payload, 'response' => $res->body(),]
            );
            return [];
        }

        if (!$data) {
            Log::warning(
                "[FUJISAT] invalid body received, invalid body",
                ["response" => $res->body(),]
            );
            return [];
        }

        if (!array_key_exists("data", $data)) {
            Log::warning(
                "[FUJISAT] invalid body received missing data",
                [
                    "response" => $res->body(),
                    "query" => $query,
                ]
            );
            return [];
        }

        if (!array_key_exists("status", $data)) {
            Log::warning(
                "[FUJISAT] invalid body received missing status",
                [
                    "response" => $res->body(),
                    "query" => $query,
                ]
            );
            return [];
        }

        if (!array_key_exists("code", $data)) {
            Log::warning(
                "[FUJISAT] invalid body received missing code",
                [
                    "response" => $res->body(),
                    "query" => $query,
                ]
            );
            return [];
        }

        $status = $data['status'];
        $code = $data['code'];

        if ($status != 200 || $code != 0) {
            Log::info("[FUJISAT] invalid status or code", [
                "response" => $res->body(),
                "query" => $query,
            ]);
            return [];
        }

        $data = $data['data'];
        $results = [];

        foreach ($data as $result) {
            $subscription = $result['optionmajeureabo'];
            $billNumber = $result['numabo'];
            $subscriberNumber = $result['clabo'];
            $name = $result['nomabo'] ?? "";
            $surname = $result['prenomabo'] ?? "";

            $name = "$name $surname";


            $results[] = new SearchResult(
                id: Uuid::uuid4()->toString(),
                billNumber: $billNumber,
                customerName: $name,
                customerNumber: $subscriberNumber,
                options: [],
                productId: null,
                description: $subscription,
            );

        }
        return $results;
    }

    public function search(Service $service, Product $product, string $query): array
    {
        if ($product->fixed_price) {
            return $this->searchSubscriber($service, $query);
        }

        $subscription = $this->searchSubscription($service, $query);

        if (is_null($subscription)) {
            return [];
        }

        return [$subscription];
    }

}