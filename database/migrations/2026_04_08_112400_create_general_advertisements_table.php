<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('general_advertisements', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('title', 160);
            $table->text('description');
            $table->string('contact_phone', 20);
            $table->boolean('contact_whatsapp')->default(false);
            $table->boolean('telegram')->default(false);
            $table->boolean('imo')->default(false);
            $table->boolean('viber')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('fake_count')->default(0);
            $table->string('status', 30)->default('pending');

            $table->foreignUuid('application_user_id')->constrained('application_users')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignUuid('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->foreignUuid('advertise_type_id')->nullable()->constrained('advertise_types')->nullOnDelete();

            $table->timestamps();
            $table->index(['status', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('general_advertisements');
    }
};
