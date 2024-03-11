<?php
use App\Models\ServiceKindEnum;
use App\Models\Transaction;
use App\Services\Payment\Status;
use App\Services\Payment\TransactionResult;
use App\Services\Payment\TransactionServiceInterface;
use Carbon\Carbon;
use Log;

class FujisatService implements TransactionServiceInterface
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
            "offreCode" => "EVDD",
            "duree" => 1,
            "amount" => $transaction->amount,
            "telephone" => "",
            "option" => []
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
        Log::info("[FUJISAT] auth response", ["body" => $res->body(), "status" => $res->status()]);
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
        if (!array_key_exists("numabo", $data['data'])) {
            throw new Exception("invalid response, missing numabo");
        }
        return $data['data']['numabo'];
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
}