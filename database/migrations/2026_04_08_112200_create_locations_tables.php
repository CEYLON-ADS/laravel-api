<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('country_name', 150);
            $table->string('country_code', 5)->nullable();
            $table->string('dial_code', 10)->nullable();
            $table->string('currency_code', 10)->nullable();
            $table->string('currency_name', 50)->nullable();
            $table->string('currency_symbol', 10)->nullable();
            $table->string('capital', 80)->nullable();
            $table->string('continent_code', 10)->nullable();
            $table->string('continent_name', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('district', 120);
            $table->foreignUuid('country_id')->constrained('countries')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['country_id', 'district']);
        });

        Schema::create('cities', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name', 120);
            $table->foreignUuid('district_id')->constrained('districts')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['district_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('countries');
    }
};
