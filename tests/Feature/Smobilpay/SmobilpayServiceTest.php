<?php

namespace Tests\Feature\Smobilpay;

use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceKindEnum;
use App\Models\Transaction;
use App\Services\Payment\TransactionResult;
use App\Services\Payment\Status;
use App\Services\Smobilpay\SmobilpayService;
use Illuminate\Http\Client\Request;
use Tests\TestCase;
use Http;
use Cache;

class SmobilpayServiceTest extends TestCase
{
    public function setUp(): void {
        parent::setUp();
        Cache::setDefaultDriver("array");
    }
    protected function getResponseStub(string $name)
    {
        $currentDir = dirname(__FILE__);
        $res = file_get_contents("$currentDir/stubs/$name");
        if (!$res) {
            throw new \Exception("Unable to locate stub [$name]");
        }
        return $res;
    }
    /**
     * @group smobil:init
     */
    public function test_payment_is_initiated(): void
    {
        $baseUrl = config("smobilpay.baseUrl");
        $username = config("smobilpay.username");
        $password = config("smobilpay.password");
        $token = "6d876381f6fe229dfc3d25e75c9b98ce:3a852cfe2833661742a637a6eba2db92d9ccbdab";
        Http::fake([
            ...$this->login_simulations($token),
            "$baseUrl/service/search" => Http::response("", 302, [
                "Date" => "Tue, 18 Jul 2023 23:30:28 GMT",
                "Content-Type" => "text/html; charset=utf-8",
                "Transfer-Encoding" => "chunked",
                "Connection" => "keep-alive",
                "Strict-Transport-Security" => "max-age=63072000; includeSubdomains; preload",
                "X-Frame-Options" => "SAMEORIGIN",
                "X-Xss-Protection" => "1; mode=block",
                "Content-Security-Policy" => "frame-ancestors 'none'",
                "Content-Encoding" => "gzip",
                "Vary" => "Accept-Encoding",
                "Location" => "$baseUrl/collection/preview/S-113-948-CMMTNMOMO-90003-200544-1",
                "Cache-Control" => "max-age=1",
                "Expires" => "Tue, 18 Jul 2023 23:30:28 GMT",
            ]),
            "$baseUrl/collection/preview/*" => Http::response("", 200),
            "$baseUrl/collectionAPI.php/quotestd?*" => Http::response([
                "systemCur" => "XAF",
                "feeAmountSystemCur" => "0.00",
                "amountSystemCur" => "100.00",
                "localCur" => "XAF",
                "feeAmountLocalCur" => "0.00",
                "amountLocalCur" => "100.00",
                "expiresAt" => "2023-07-19T01:36:52+01:00",
                "quoteId" => "b26eb07c-f1c9-4d85-ab2e-4c84840b6396",
                "payItemId" => "S-113-948-CMMTNMOMO-90003-200544-1",
                "promotion" => null
            ], 200),
            "$baseUrl/collection/collect" => Http::response([
                "ptn" => "le ptn",
                "status" => "Success"
            ], 200),
            "*" => Http::response(status: 500)
        ]);

        /** @var SmobilpayService */
        $smobil = $this->app->get(SmobilpayService::class);

        $service = new Service;
        $service->name = "MTN Mobile Money Collection";
        $service->kind = ServiceKindEnum::payment->value;
        $service->fixed_amount = false;
        
        
        $product = new Product;
        $product->service = $service;
        $product->provider_id_1 = 'some_external_id_1';
        $product->provider_id_2 = 'some_external_id_1';
        $product->color = "yellow";
        $product->name = "Paiement MTN Mobile Money";
        $product->description = $product->name;
        $product->fixed_price = false;
        $product->enabled = true;

        $transaction = new Transaction;
        

        $transaction->amount = 50;
        $transaction->product = $product;
        $transaction->destination = "650675795";

        $result = $smobil->initiate($transaction);

        // loading csrf
        Http::assertSent(
            fn(Request $request): bool =>
            $request->url() === "$baseUrl/login" && $request->method() === "GET"
        );
        // login request
        Http::assertSent(
            fn(Request $request): bool =>
            $request->url() === "$baseUrl/login" &&
            $request->method() === "POST"
            && $request->body() === http_build_query([
                "signin[_csrf_token]" => "db59f435ec203111273d6df5d1d6b6ab",
                "signin[username]" => $username,
                "signin[password]" => $password,
                "login" => "LOG IN"
            ])

        );
        Http::assertSent(
            fn(Request $request): bool => 
            $request->url() === "$baseUrl/service/search"
            && !empty($request->header("Cookie"))
            && str_contains($request->header("Cookie")[0], $token)
            && $request->method() === "POST"
            && $request->data() === [
                [
                    "name" => "payment_search[productref]",
                    "contents" => $product->provider_id_2,
                ],
                [
                    "name" => "payment_search[amount]",
                    "contents" => $transaction->amount,
                ],
                [
                    "name" => "payment_search[svc_nmb]",
                    "contents" => $transaction->destination,
                ],
                ["name" => "service_id", "contents" => $product->provider_id_1],
            ]

        );
        $this->assertEquals(
            $result,
            new TransactionResult(
                Status::PENDING,
                "le ptn"
            )
        );
    }

    /**
     * @group smobil:status
     */
    public function test_status_check()
    {
        $transaction = new Transaction;
        $transaction->external_reference = "some_reference";
        $transaction->status = Status::PENDING->value;
        Http::fake([
            ...$this->login_simulations("some_token"),
            ...$this->status_check_simulations($transaction),
            "*" => Http::response(status: 500)
        ]);
        /** @var SmobilpayService */
        $smobil = $this->app->get(SmobilpayService::class);

        $status = $smobil->checkStatus($transaction);
        $this->assertEquals(
            new TransactionResult(Status::PENDING),
            $status,
        );
    }

    /** @group smobil:cache */
    public function test_session_cookie_is_kept_in_cache(): void
    {
        $transaction = new Transaction;
        $transaction->external_reference = "some_reference";
        $transaction->status = Status::PENDING->value;
        Cache::shouldReceive('remember')
            ->once()
            ->with('app:smobilpay:token', 600, \Mockery::type('closure'))
            ->andReturn('some_token');
        Http::fake([
            ...$this->login_simulations("some_token"),
            ...$this->status_check_simulations($transaction),
            "*" => Http::response(status: 500)
        ]);
        /** @var SmobilpayService */
        $smobil = $this->app->get(SmobilpayService::class);
        $smobil->checkStatus($transaction);
    }


    protected function status_check_simulations(Transaction $transaction, string $expectedStatus = "PENDING"): array
    {
        $baseUrl = config("smobilpay.baseUrl");
        return [
            "$baseUrl/get-payment-status?ptn={$transaction->external_reference}" => Http::response([
                "ptn" => "99999169012359000020394713002259",
                "status" => $expectedStatus
            ], 200)
        ];
    }

    protected function login_simulations(string $token): array
    {
        $baseUrl = config("smobilpay.baseUrl");
        return [
            "$baseUrl/login" => Http::sequence()
                ->push(
                    $this->getResponseStub("login.stub.html"),
                    401,
                    [
                        "Set-Cookie" => "smopamobilpay=$token; expires=Tue, 18-Jul-2023 23:09:44 GMT; Max-Age=3600; path=/; domain=.smobilpay.com; secure; HttpOnly",
                    ]
                )
                ->push(
                    "",
                    302,
                    [
                        "Server" => "nginx/1.1.19",
                        "Date" => "Tue, 18 Jul 2023 22:09:44 GMT",
                        "Content-Type" => "text/html; charset=utf-8",
                        "Transfer-Encoding" => "chunked",
                        "Connection" => "keep-alive",
                        "Strict-Transport-Security" => "max-age=63072000; includeSubdomains; preload",
                        "X-Frame-Options" => "SAMEORIGIN",
                        "X-Xss-Protection" => "1; mode=block",
                        "Content-Security-Policy" => "frame-ancestors 'none'",
                        "Content-Encoding" => "gzip",
                        "Vary" => "Accept-Encoding",
                        "Set-Cookie" => "smopamobilpay=$token; expires=Tue, 18-Jul-2023 23:09:44 GMT; Max-Age=3600; path=/; domain=.smobilpay.com; secure; HttpOnly",
                        "Location" => "https://cm.smobilpay.com/login",
                        "Cache-Control" => "max-age=1",
                        "Expires" => "Tue, 18 Jul 2023 22:09:43 GMT",
                    ]
                ),
        ];
    }
}