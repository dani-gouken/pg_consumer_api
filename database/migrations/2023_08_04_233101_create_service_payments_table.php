<?php

use App\Models\Product;
use App\Models\Service;
use App\Models\ServicePaymentStatusEnum;
use App\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->enum(
                "status",
                array_map(fn(ServicePaymentStatusEnum $e) => $e->value, ServicePaymentStatusEnum::cases())
            );
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(Service::class);
            $table->foreignIdFor(Service::class,"payment_service_id");
            $table->string("notification_email")->nullable();
            $table->string("code")->unique();
            $table->string("notification_phone_number")->nullable();
            $table->string("customer_name")->default("");
            $table->string("credit_destination")->default("");
            $table->string("debit_destination")->default("");
            $table->decimal("amount");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_payments');
    }
};
