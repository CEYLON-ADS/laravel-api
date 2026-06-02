<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_advertisement_cities', function (Blueprint $table): void {
            $table->uuid('general_advertisement_id');
            $table->uuid('city_id');
            $table->primary(['general_advertisement_id', 'city_id']);
            $table->foreign('general_advertisement_id')->references('id')->on('general_advertisements')->cascadeOnDelete();
            $table->foreign('city_id')->references('id')->on('cities')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_advertisement_cities');
    }
};
