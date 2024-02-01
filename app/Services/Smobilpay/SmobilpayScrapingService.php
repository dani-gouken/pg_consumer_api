<?php
namespace App\Services\Smobilpay;

use App\Models\Product;
use App\Models\Service;
use App\Models\Transaction;
use App\Services\Payment\Exceptions\TransactionInitFailureException;
use App\Services\Payment\Exceptions\TransactionStatusCheckFailureException;
use App\Services\Payment\TransactionResult;
use App\Services\Payment\Status;
use App\Services\Payment\TransactionServiceInterface;
use Cache;
use Log;

class SmobilpayScrapingService implements TransactionServiceInterface
{
    const AUTH_COOKIE_NAME = "smopamobilpay";
    use Client;
    public function __construct(
        protected string $username,
        protected string $password,
        protected string $baseUrl,
        protected string $cachePrefix = "app",
        protected int $cacheDurationInSeconds = 600
    ) {

    }

    public function initiate(Transaction $tx): TransactionResult
    {
        $token = $this->getToken();
        $payItemId = $this->makePayItemId(
            $tx->product,
            $token,
            $tx->destination,
            $tx->amount
        );
        $this->quote($token, $payItemId, $tx->amount);
        $ptn = $this->collect($token, $tx->amount);
        return new TransactionResult(Status::PENDING, $ptn);
    }

    public function checkStatus(Transaction $transaction, bool $isRetry = false): TransactionResult
    {
        if ($transaction->getStatus()->isFinal()) {
            throw new TransactionStatusCheckFailureException("cannot check the status of a final transaction");
        }
        if (!$transaction->external_reference) {
            throw new TransactionStatusCheckFailureException("cannot check the status of a transaction without a reference");
        }
        $token = $this->getToken();
        $response = $this->client($token)
            ->get("{+baseUrl}/get-payment-status", ["ptn" => $transaction->external_reference]);
        if (!$response->successful()) {
            if (($response->status() === 401) && !$isRetry) {
                \Log::error("token has maybe expired");
                $this->logout();
                $this->checkStatus($transaction, true);
            }
            \Log::error("failed to get payment status", ["statusCode" => $response->status(), "body" => $response->body()]);
            throw new TransactionStatusCheckFailureException("status check failed", $response, 10);
        }
        $data = $response->json();
        if (is_null($data) || !is_array($data) || !array_key_exists("status", $data)) {
            Log::info("malformed response received during status check", [
                "body" => $response->body(),
                "response" => $response
            ]);
            throw new TransactionStatusCheckFailureException("malformed response received during status check", $response, 10);
        }
        $status = $data["status"];
        return match ($status) {
            "ERRORED", "CANCELLED" => new TransactionResult(Status::ERROR),
            "SUCCESS", "SUCCESSFUL" => new TransactionResult(Status::SUCCESS),
            default => new TransactionResult(Status::PENDING)
        };
    }
    protected function collect(string $token, int $amount, bool $isRetry = false)
    {
        $payload = [
            "email" => "",
            "phonenumber" => "",
            "ref" => "",
            "amount" => $amount,
            "method" => "502",
        ];
        Log::info("executing collection", $payload);
        $response = $this->client($token)
            ->asForm()
            ->post("{+baseUrl}/collection/collect", $payload);
        if (!$response->successful()) {
            if (($response->status() == 401) && !$isRetry) {
                $this->logout();
                return $this->collect(
                    $token,
                    $amount,
                    true
                );
            }
            throw new TransactionInitFailureException(
                "Collection request was not successful",
                $response
            );
        }
        $data = $response->json();
        if (!is_array($data) || !array_key_exists("ptn", $data)) {
            Log::info("invalid response received while doing collection", [
                "response" => $response->body()
            ]);
            throw new TransactionInitFailureException(
                "could not retreived PTN in collection response",
                $response
            );
        }
        $ptn = $data["ptn"];
        Log::info("collection executed", compact("ptn"));
        return $ptn;
    }
    protected function quote(string $token, string $payItemId, int $amount, bool $isRetry = false): string
    {
        $payload = [
            "payItemId" => $payItemId,
            "payMethodId" => "502",
            "amount" => $amount,
        ];
        Log::info("creating quote", $payload);
        $response = $this->client($token)
            ->withHeader("apiKey", "OTU0ZGU1ZTMtZmRmYy00NjU0LWE2NTItODkwZTJjM2UzZGRiOjI3QTE1QjVDLTVFQzYtMUY5MC0xRTE5LTRFOTQ4Q0Y0QUUzOQ==")
            ->get("{+baseUrl}/collectionAPI.php/quotestd", $payload);
        if (!$response->successful()) {
            if (($response->status() == 401) && !$isRetry) {
                $this->logout();
                return $this->quote(
                    $token,
                    $payItemId,
                    $amount,
                    true
                );
            }
            throw new TransactionInitFailureException(
                "Quote request was not successful",
                $response
            );
        }
        $data = $response->json();

        if (!is_array($data) || !array_key_exists("quoteId", $data)) {
            Log::error("invalid response after quote", $data);
            throw new TransactionInitFailureException(
                "Invalid response received after quote response was successful",
                $response
            );
            ;
        }
        $quoteId = $data["quoteId"];
        Log::info("quote created", compact("quoteId"));
        return $quoteId;
    }
    protected function makePayItemId(
        Product $product,
        string $token, string $destination, int $amount,
        bool $isRetry = false,
    ): string {
        $payload = [
            "payment_search[productref]" => $product->provider_id_2,
            "payment_search[amount]" => $amount,
            "payment_search[svc_nmb]" => $destination,
            "service_id" => $product->provider_id_1
        ];
        Log::info("retrieve payItemId", $payload);
        $response = $this->client($token)
            ->withOptions([
                'allow_redirects' => true,
            ])
            ->asMultipart()
            ->post("{+baseUrl}/service/search", $payload);
        if (!$response->successful()) {
            if (($response->status() == 401) && !$isRetry) {
                $this->logout();
                return $this->makePayItemId(
                    $product,
                    $token,
                    $destination,
                    $amount,
                    true
                );
            }
            throw new TransactionInitFailureException(
                "Service search did not succeed",
                $response
            );
        }
        $location = $response->effectiveUri()->__toString();
        $matches = [];
        preg_match_all(
            '/.*\/preview\/(\S+)/',
            $location,
            $matches
        );
        if (count($matches) !== 2 || count($matches[1]) !== 1) {
            throw new TransactionInitFailureException(
                sprintf("Unable to extract payItemId from url after service search. location: %s", $location),
                $response
            );
        }
        $payItemId = $matches[1][0];
        Log::info("payItemId retrieved", compact("payItemId"));
        return $payItemId;
    }
    protected function getToken(): string
    {
        $key = "{$this->cachePrefix}:smobilpay:token";
        return Cache::remember($key, $this->cacheDurationInSeconds, $this->login(...));
    }

    protected function logout(): bool
    {
        $key = "{$this->cachePrefix}:smobilpay:token";
        return Cache::forget($key);
    }
    protected function login(): string
    {
        $res = $this->getCsrfToken();
        $csrf = $res["csrf"];
        $token = $res["token"];
        Log::info("CSRF token retrieved", $res);
        $payload = [
            "signin[_csrf_token]" => $csrf,
            "signin[username]" => $this->username,
            "signin[password]" => $this->password,
            "login" => "LOG IN"
        ];
        Log::info("logging in", $payload);
        $response = $this->client($token)
            ->asForm()->post("{+baseUrl}/login", $payload);
        if (!$response->redirect()) {
            throw new TransactionInitFailureException(
                "Login did not returned a 302 redirect response",
                $response
            );
        }
        $setCookie = $response->cookies->getCookieByName(self::AUTH_COOKIE_NAME);
        if (!$setCookie) {
            throw new TransactionInitFailureException(
                "Missing auth cookie in auth response while logging in",
                $response
            );
        }
        Log::info("login succesful", ["session token" => $setCookie->getValue()]);
        return $setCookie->getValue();
    }

    protected function getCsrfToken(): array
    {
        Log::info("loading csrf token");
        $response = $this->client()->get("{+baseUrl}/login");
        if (!$response->unauthorized()) {
            throw new TransactionInitFailureException(
                "login page did not returned 401 status, maybe the website is not available",
                $response
            );
        }
        $body = $response->body();
        $matches = [];
        preg_match_all(
            '/<input.*name="signin\[_csrf_token\]".*value="(\S+)".*>/',
            $body,
            $matches
        );
        if (count($matches) !== 2 || count($matches[1]) !== 1) {
            throw new TransactionInitFailureException(
                "unable to retrieve CSRF token in the HTML of the login page",
                $response
            );
        }
        $authCookie = $response->cookies->getCookieByName(self::AUTH_COOKIE_NAME);
        if (!$authCookie) {
            throw new TransactionInitFailureException(
                "unable to retrieve auth cookie in login page, while loading CSRF",
                $response
            );
        }
        return ["csrf" => $matches[1][0], "token" => $authCookie->getValue()];
    }

}