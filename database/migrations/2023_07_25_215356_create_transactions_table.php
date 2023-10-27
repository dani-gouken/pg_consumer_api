<?php

use App\Models\Product;
use App\Models\Service;
use App\Models\ServicePayment;
use App\Models\TransactionKind;
use App\Services\Payment\Status;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(Service::class);
            $table->foreignIdFor(ServicePayment::class)->nullable();
            $table->uuid("uuid");
            $table->integer("status_check_count")->default(0);
            $table->integer("max_status_check");
            $table->integer("amount");
            $table->string("external_reference")->nullable();
            $table->enum(
                "status",
                array_map(fn(Status $s) => $s->value, Status::cases())
            );
            $table->enum(
                "kind",
                array_map(fn(TransactionKind $s) => $s->value, TransactionKind::cases())
            );
            $table->string("secret")->nullable();
            $table->string("error")->nullable();
            $table->string("provider_error")->nullable();
            $table->string("destination");
            $table->timestamp("last_status_check_at")->nullable();
            $table->timestamp("processed_at")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
