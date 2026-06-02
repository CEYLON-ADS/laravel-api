<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_user_id')->constrained('application_users')->cascadeOnDelete();
            $table->string('otp_code', 6);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['application_user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
