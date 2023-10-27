<?php

namespace Tests\Feature\Payment;

use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceKindEnum;
use App\Models\Transaction;
use App\Models\TransactionKind;
use App\Services\Payment\TransactionProcessor;
use App\Services\PaymentGatewayService;
use App\Services\Payment\Status;
use App\Services\Payment\TransactionResult;
use Database\Seeders\ServicesSeeder;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Tests\TestCase;
use Bus, Queue, Http;

/** @group pgService */
class PaymentGatewayServiceTest extends TestCase
{
    use RefreshDatabase;
    public function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        Queue::fake();
        Event::fake();
    }
    /** @group pgService:init */
    public function test_initiate(): void
    {
        $baseUrl = config("pg.baseUrl");
        $privateKey = config("pg.privateKey");
        $publicKey = config("pg.publicKey");

        $this->seed(ServicesSeeder::class);
        $pgService = $this->app->get(PaymentGatewayService::class);
        /** @var TransactionProcessor */
        $txProcessor = $this->app->get(TransactionProcessor::class);
        $product = Product::where('name', 'Crédit Blue/Camtel')->firstOrFail();
        $transaction = $txProcessor->createTransaction(
            $product,
            "620272723",
            TransactionKind::credit,
            100,
        );

        Http::fake([...$this->init_simulations()]);
        $result = $pgService->initiate($transaction);

        // login request
        Http::assertSent(
            fn(Request $request): bool =>
            ($request->header('Authorization')[0] === "Bearer {$privateKey}") &&
            $request->url() === "$baseUrl/payment" &&
            $request->method() === "POST"
            && $request->body() === json_encode([
                "publicKey" => $publicKey,
                "amount" => 100,
                "reference" => $transaction->uuid,
                "country" => "cm",
                "recipient" => "237620272723",
                "channel" => "CHANNEL_CAMTEL",
                "type" => "payout"
            ])

        );

        $this->assertEquals(
            $result,
            new TransactionResult(
                Status::PENDING,
                "someid"
            )
        );
    }

    /** @group pgService:status */
    public function test_status_checks(): void
    {
        $baseUrl = config("pg.baseUrl");
        $privateKey = config("pg.privateKey");
        $publicKey = config("pg.publicKey");

        $this->seed(ServicesSeeder::class);
        /** @var PaymentGatewayService */
        $pgService = $this->app->get(PaymentGatewayService::class);
        /** @var TransactionProcessor */
        $txProcessor = $this->app->get(TransactionProcessor::class);
        $product = Product::where('name', 'Crédit Blue/Camtel')->firstOrFail();
        $transaction = $txProcessor->createTransaction(
            $product,
            "620272723",
            TransactionKind::credit,
            100,
        );
        $transaction->external_reference = "someid";
        Http::fake([...$this->status_check_simulations()]);
        $result = $pgService->checkStatus($transaction);

        // login request
        Http::assertSent(
            fn(Request $request): bool =>
            ($request->header('Authorization')[0] === "Bearer {$privateKey}") &&
            $request->url() === "$baseUrl/payment/someid" &&
            $request->method() === "GET"
        );

        $this->assertEquals(
            $result,
            new TransactionResult(
                Status::ERROR,
                "someid",
                'somedescription',
                'someerror'
            )
        );
    }

    protected function init_simulations(): array
    {
        $baseUrl = config("pg.baseUrl");
        return [
            "$baseUrl/payment/*" => Http::response(
                json_encode([
                    'success' => true,
                    'code' => 601,
                    'locale' => 'en',
                    'message' => 'transaction.update.accepted',
                    'data' => [
                        'transaction' => [
                            'amount_received' => NULL,
                            'amount' => 100,
                            'status' => 'WAITING_FOR_PAYMENT',
                            'type' => 'payout',
                            'user_reference' => 'F824e76dc-b96f-42c4-a2da-852631257ae8',
                            'uuid' => 'someid',
                            'payment_method_code' => 'CAMTEL',
                            'currency_code' => 'XAF',
                            'country_code' => 'CM',
                            'recipient' => '237621259601',
                            'created_at' => '2023-10-25T12:08:56.000000Z',
                            'updated_at' => '2023-10-25T12:19:18.000000Z',
                            'provider_error_code' => NULL,
                            'error_message' => NULL,
                        ],
                    ],
                ]),
                202
            ),
            "$baseUrl/payment" => Http::sequence()
                ->push(
                    json_encode([
                        'success' => true,
                        'code' => 602,
                        'locale' => 'en',
                        'message' => 'transaction.initiated',
                        'data' => [
                            'transaction' => [
                                'amount_received' => NULL,
                                'amount' => 100,
                                'status' => 'INITIATED',
                                'type' => 'payout',
                                'user_reference' => 'F824e76dc-b96f-42c4-a2da-852631257ae8',
                                'uuid' => 'someid',
                                'payment_method_code' => 'CAMTEL',
                                'currency_code' => 'XAF',
                                'country_code' => 'CM',
                                'recipient' => '237621259601',
                                'status_info' => [
                                    'label' => 'INITIATED',
                                    'code' => '2003',
                                ],
                                'description' => '',
                                'is_mock' => 0,
                                'created_at' => '2023-10-25T12:08:56.000000Z',
                                'updated_at' => '2023-10-25T12:08:56.000000Z',
                                'provider_error_code' => NULL,
                                'error_message' => NULL,
                                'payment_promise' => NULL,
                                'payment_method' => [
                                    'code' => 'CAMTEL',
                                    'name' => 'Camtel Blue',
                                ],
                            ],
                            'schema_type' => 'PHONE_NUMBER',
                            'schema_description' => [
                                'item' => [
                                    'phoneNumber' => [
                                        'optional' => false,
                                        'format' => [
                                            'type' => 'CM_PHONE_NUMBER',
                                            'description' => 'A valid cameroonian phone number starting with 237',
                                            'example' => '+237691111111',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]),
                    201
                )
        ];
    }
    protected function status_check_simulations(): array
    {
        $baseUrl = config("pg.baseUrl");
        return [
            "$baseUrl/payment/*" => Http::response(
                json_encode([
                    'success' => true,
                    'code' => 601,
                    'locale' => 'en',
                    'message' => 'transaction.update.accepted',
                    'data' => [
                        'transaction' => [
                            'amount_received' => NULL,
                            'amount' => 100,
                            'status' => 'FAILED',
                            'type' => 'payout',
                            'user_reference' => 'F824e76dc-b96f-42c4-a2da-852631257ae8',
                            'uuid' => 'someid',
                            'payment_method_code' => 'CAMTEL',
                            'currency_code' => 'XAF',
                            'country_code' => 'CM',
                            'recipient' => '237621259601',
                            'created_at' => '2023-10-25T12:08:56.000000Z',
                            'updated_at' => '2023-10-25T12:19:18.000000Z',
                            'provider_error_code' => 'somedescription',
                            'error_message' => 'someerror',
                        ],
                    ],
                ]),
                200
            ),
        ];
    }
}