<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('mobile_number', 20)->unique();
            $table->string('name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_users');
    }
};
