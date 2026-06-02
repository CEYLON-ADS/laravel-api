<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('username', 120)->unique();
            $table->string('password');
            $table->string('role', 30)->default('admin');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_users');
    }
};
