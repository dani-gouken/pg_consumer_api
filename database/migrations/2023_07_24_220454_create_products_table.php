<?php

use App\Models\Service;
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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Service::class);
            $table->uuid("uuid");
            $table->string("color");
            $table->string("name");
            $table->string("description");
            $table->string("slug");
            $table->boolean("default")->default(false);
            $table->string("provider_id_1")->nullable();
            $table->string("provider_id_2")->nullable();
            $table->integer('fixed_price')->default(false);
            $table->integer('price')->nullable();
            $table->boolean("enabled")->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
