<?php

use App\Models\ServiceKindEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid")->unique();
            $table->string("image");
            $table->string("name");
            $table->string("slug")->unique();
            $table->string("description");
            $table->string("form_input_label");
            $table->string("form_input_placeholder");
            $table->string("form_input_regex");
            $table->string("provider_id_1")->nullable();
            $table->string("provider_id_2")->nullable();
            $table->enum('kind', array_map(fn(ServiceKindEnum $e) => $e->value, ServiceKindEnum::cases()));
            $table->boolean("enabled")->default(false);
            $table->boolean("public")->default(false);
            $table->string("provider");
            $table->integer("min_amount")->nullable();
            $table->integer("max_amount")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};