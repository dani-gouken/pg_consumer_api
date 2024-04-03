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
use Log;
use Cache;
use Http;
use Exception;
use LogicException;
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

    protected function makeRequest(?string $token = null)
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
        $status = $data["status"];
        if ($status == 200) {
            return new TransactionResult(Status::SUCCESS);
        }
        return new TransactionResult(Status::ERROR, providerError: $data['data'] ?? "payment failed");
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
            throw new \Exception("Failed to generate a new token");
        }

        if (!array_key_exists("data", $data)) {
            throw new \Exception($res, "invalid response received from client search endpoint");
        }

        $data = $data['data'];

        if (empty($data)) {
            throw new \Exception($res, "num abo not found");
        }

        $data = $data[0];

        if (!array_key_exists("numabo", $data)) {
            throw new Exception("invalid response, missing numabo");
        }
        return $data['numabo'];
    }

    /**
     * @return array<{product:Product,options:Option}>
     */
    public function getProducts(Service $service): array
    {
        $token = $this->getToken();
        $req = $this->makeRequest($token)->get('/operations/offers');
        Log::info("[FUJISAT] auth response", ["body" => $req->body(), "status" => $req->getStatusCode()]);
        $data = $req->json();
        if ($req->failed()) {
            Log::info("[FUJISAT] options request failed", ["body" => $req->body(), "status" => $req->getStatusCode()]);
            return [];
        }
        if (!array_key_exists("data", $data) || !is_array($data)) {
            Log::warning(
                "[FUJISAT] invalid response received from get token endpoint",
                ["body" => $req->body(), "status" => $req->getStatusCode()]
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
        Log::info("[FUJISAT] auth response", ["body" => $req->body(), "status" => $req->getStatusCode()]);
        $data = $req->json();
        if ($req->failed()) {
            Log::info("[FUJISAT] token request failed", ["body" => $req->body(), "status" => $req->getStatusCode()]);
            return null;
        }
        if (!$data) {
            Log::warning(
                "[FUJISAT] invalid body received while generating a token",
                ["body" => $req->body(), "status" => $req->getStatusCode()]
            );
            return null;
        }
        if (!array_key_exists("data", $data)) {
            Log::warning(
                "[FUJISAT] invalid response received from get token endpoint",
                ["body" => $req->body(), "status" => $req->getStatusCode()]
            );
            return null;
        }
        if (!array_key_exists("token", $data['data']) && !array_key_exists("tokenExpirationDate", $data['data'])) {
            Log::warning(
                "[FUJISAT] invalid response, missing token or expiry date",
                ["body" => $req->body(), "status" => $req->getStatusCode()]
            );
            return null;
        }
        $token = $data['data']["token"];
        $tokenExpirationDate = (int) $data['data']["tokenExpirationDate"];
        $lifetime = Carbon::parse($tokenExpirationDate)->diff(now())->s;
        Cache::put(self::FUJISAT_TOKEN_KEY, $token, $lifetime);

        return $token;
    }

    public function search(Service $service, string $query): ?SearchResult
    {
        $token = $this->getToken();
        Log::info("[FUJISAT] sending subscriber search request", [
            "query" => $query,
        ]);
        $res = $this->makeRequest($token)->get('/business/reabonnement', $payload = ["device" => $query,]);
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
                "[FUJISAT] invalid body received while generating a token",
                ["response" => $res->body(),]
            );
            return null;
        }

        if (!array_key_exists("data", $data)) {
            return null;
        }

        $data = $data['data'];
        $subscription = $data['offreCodeLC'];
        $product = $service->enabledProductsQuery()->where('provider_id_1', $subscription)->first();
        if ($product == null) {
            Log::warning(
                "[FUJISAT] product not found for subscription code [$subscription]",
                ["response" => $res->body()]
            );
            return null;
        }

        return new SearchResult($product);
    }

}