<?php

namespace Tests\Feature\Payment;

use App\Jobs\StatusCheck;
use App\Models\Product;
use App\Models\TransactionKind;
use App\Events\TransactionCompleted;
use App\Services\Payment\Exceptions\TransactionInitFailureException;
use App\Services\Payment\TransactionProcessor;
use App\Services\Payment\Status;
use App\Services\Payment\TransactionResult;
use App\Services\Payment\TransactionServiceInterface;
use App\Services\Payment\TransactionServiceResolverInterface;
use Database\Seeders\ServicesSeeder;
use Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Services\Payment\DummyTransactionService;
use Tests\TestCase;
use Bus, Queue;

class TransactionProcessorTest extends TestCase
{
    use RefreshDatabase;
    public function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        Queue::fake();
        Event::fake();
    }
    public function test_create(): void
    {
        $this->seed(ServicesSeeder::class);
        $product = Product::first();
        /** @var TransactionProcessor */
        $processor = $this->app->makeWith(TransactionProcessor::class, [
            'delayBetweenStatusCheck' => 10,
            'maximumStatusCheck' => 35,
        ]);

        $product->fixed_price = false;
        $tx = $processor->createTransaction(
            $product,
            $destination = "foo_bar",
            TransactionKind::credit,
            $amount = 100
        );
        $this->assertEquals($amount, $tx->amount);
        $this->assertEquals($destination, $tx->destination);
        $this->assertEquals($product->service->id, $tx->service_id);
        $this->assertEquals($product->id, $tx->product_id);
        $this->assertEquals(0, $tx->status_check_count);
        $this->assertEquals(35, $tx->max_status_check);
        $this->assertEquals(Status::PENDING, $tx->status);
        $tx->save();
        $this->assertIsInt($tx->id);
    }

    public function test_create_fixed_price(): void
    {
        $this->seed(ServicesSeeder::class);
        $product = Product::first();
        $processor = $this->app->makeWith(TransactionProcessor::class);
        $product->fixed_price = true;
        $product->price = 200;
        $tx = $processor->createTransaction($product, "foo_bar", TransactionKind::credit);
        $this->assertEquals($product->price, $tx->amount);
    }

    public function test_create_throw_if_not_fixed_price_without_amount(): void
    {
        $this->seed(ServicesSeeder::class);
        $product = Product::first();
        $processor = $this->app->makeWith(TransactionProcessor::class);
        $product->fixed_price = false;
        $this->expectException(TransactionInitFailureException::class);
        $tx = $processor->createTransaction($product, "foo_bar", TransactionKind::credit);
        $this->assertEquals($product->price, $tx->amount);
    }

    public function test_create_min_amount(): void
    {
        $this->seed(ServicesSeeder::class);
        $product = Product::first();
        $service = $product->service;
        $processor = $this->app->makeWith(TransactionProcessor::class);
        $product->fixed_price = false;

        $service->min_amount = 200;
        $this->expectException(TransactionInitFailureException::class);
        $tx = $processor->createTransaction($product, "foo_bar", TransactionKind::credit, 100);
        $this->assertEquals($product->price, $tx->amount);
    }

    public function test_create_max_amount(): void
    {
        $this->seed(ServicesSeeder::class);
        $product = Product::first();
        $service = $product->service;
        $processor = $this->app->makeWith(TransactionProcessor::class);
        $product->fixed_price = false;

        $service->max_amount = 200;
        $this->expectException(TransactionInitFailureException::class);
        $tx = $processor->createTransaction($product, "foo_bar", TransactionKind::credit, 1000);
        $this->assertEquals($product->price, $tx->amount);
    }

    public function test_process_pending(): void
    {
        $this->seed(ServicesSeeder::class);
        $product = Product::first();
        /** @var TransactionProcessor */
        $processor = $this->makeService(
            new TransactionResult(
                Status::PENDING,
                externalReference: "some_external_reference"
            ),
            ['delayBetweenStatusCheck' => 10, "maximumStatusCheck" => 4]
        );
        $tx = $processor->createTransaction(
            $product,
            "foobar", TransactionKind::credit,
            1000
        );
        $tx = $processor->process($tx);
        $this->assertEquals($tx->status, Status::PENDING);
        Bus::assertDispatched(fn(StatusCheck $job) => $job->getTransaction()->id === $tx->id && $job->delay === 10);
        $this->assertEquals($tx->external_reference, "some_external_reference");
        $this->assertEquals(0, $tx->status_check_count);
        $this->assertNotNull($tx->last_status_check_at);
    }


    public function test_process_error(): void
    {
        $this->seed(ServicesSeeder::class);
        $product = Product::first();
        /** @var TransactionProcessor */
        $processor = $this->makeService(
            new TransactionResult(
                Status::ERROR,
                error: "someerror",
                providerError: "someprovidererror"
            )
        );
        $tx = $processor->createTransaction(
            $product,
            "foobar", TransactionKind::credit,
            1000
        );
        $tx = $processor->process($tx);
        $this->assertEquals($tx->status, Status::ERROR);
        $this->assertEquals($tx->error, "someerror");
        $this->assertEquals($tx->provider_error, "someprovidererror");
        $this->assertNotNull($tx->processed_at);
        Event::assertDispatched(fn(TransactionCompleted $e) => $e->getTransaction()->id == $tx->id);
    }

    public function test_process_success(): void
    {
        $this->seed(ServicesSeeder::class);
        $product = Product::first();
        /** @var TransactionProcessor */
        $processor = $this->makeService(
            new TransactionResult(
                Status::SUCCESS,
            )
        );
        $tx = $processor->createTransaction(
            $product,
            "foobar", TransactionKind::credit,
            1000
        );
        $tx = $processor->process($tx);
        $this->assertEquals($tx->status, Status::SUCCESS);
        $this->assertNotNull($tx->processed_at);
        Event::assertDispatched(fn(TransactionCompleted $e) => $e->getTransaction()->id == $tx->id);
    }

    public function test_status_check_pending(): void
    {
        $this->seed(ServicesSeeder::class);
        $product = Product::first();
        /** @var TransactionProcessor */
        $processor = $this->makeService(
            new TransactionResult(
                Status::PENDING,
            ),
            ['delayBetweenStatusCheck' => 10, "maximumStatusCheck" => 4]
        );
        $tx = $processor->createTransaction($product, "foobar", TransactionKind::credit, 1000);
        $tx = $processor->checkStatus($tx);
        $this->assertEquals($tx->status, Status::PENDING);
        $this->assertNotNull($tx->last_status_check_at);
        $this->assertEquals(1, $tx->status_check_count);
        Bus::assertDispatched(fn(StatusCheck $job) => $job->getTransaction()->id === $tx->id && $job->delay === 10);
    }

    public function test_status_check_success(): void
    {
        $this->seed(ServicesSeeder::class);
        $product = Product::first();
        /** @var TransactionProcessor */
        $processor = $this->makeService(
            new TransactionResult(
                Status::SUCCESS,
            )
        );
        $tx = $processor->createTransaction(
            $product,
            "foobar", TransactionKind::credit,
            1000
        );
        $tx = $processor->checkStatus($tx);
        $this->assertEquals($tx->status, Status::SUCCESS);
        $this->assertNotNull($tx->processed_at);
        Event::assertDispatched(fn(TransactionCompleted $e) => $e->getTransaction()->id == $tx->id);
    }

    public function test_status_check_error(): void
    {
        $this->seed(ServicesSeeder::class);
        $product = Product::first();
        $serviceMock = $this->createMock(TransactionServiceInterface::class);
        $serviceMock->method("checkStatus")
            ->willReturnCallback(fn() => throw new \Exception("oh no!"));
        $resolver = $this->createMock(TransactionServiceResolverInterface::class);
        $resolver->method("resolve")
            ->willReturn($serviceMock);
        /** @var TransactionProcessor */
        $processor = $this->app->makeWith(TransactionProcessor::class, array_merge([
            "resolver" => $resolver,
        ]));
        $tx = $processor->createTransaction($product, "foobar", TransactionKind::credit, 1000);
        $tx = $processor->checkStatus($tx);
        $this->assertEquals($tx->status, Status::PENDING);
    }


    public function test_status_check_timeout(): void
    {
        $this->seed(ServicesSeeder::class);
        $product = Product::first();
        /** @var TransactionProcessor */
        $processor = $this->makeService(
            new TransactionResult(
                Status::PENDING,
            ),
            ['delayBetweenStatusCheck' => 10, "maximumStatusCheck" => 4]
        );
        $tx = $processor->createTransaction($product, "foobar", TransactionKind::credit, 1000);
        $tx->status_check_count = 4;
        $tx = $processor->checkStatus($tx);
        $this->assertEquals($tx->status, Status::ERROR);
        $this->assertEquals("Payment timeout", $tx->error);
        Event::assertDispatched(fn(TransactionCompleted $e) => $e->getTransaction()->id == $tx->id);
    }

    private function makeResolver(TransactionResult $transactionResult): TransactionServiceResolverInterface
    {
        $resolver = $this->createMock(TransactionServiceResolverInterface::class);
        $resolver->method("resolve")
            ->willReturn(new DummyTransactionService($transactionResult));
        /** @var TransactionServiceResolverInterface $resolver */
        return $resolver;
    }

    private function makeService(TransactionResult $transactionResult, array $extraParams = [])
    {
        return $this->app->makeWith(TransactionProcessor::class, array_merge([
            "resolver" => $this->makeResolver(
                $transactionResult
            ),
        ], $extraParams));
    }
}