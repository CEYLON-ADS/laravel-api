<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_advertisements', function (Blueprint $table): void {
            $table->string('contact_whatsapp_number', 40)->nullable()->after('contact_whatsapp');
            $table->string('telegram_number', 40)->nullable()->after('telegram');
            $table->string('imo_number', 40)->nullable()->after('imo');
            $table->string('viber_number', 40)->nullable()->after('viber');
        });
    }

    public function down(): void
    {
        Schema::table('general_advertisements', function (Blueprint $table): void {
            $table->dropColumn([
                'contact_whatsapp_number',
                'telegram_number',
                'imo_number',
                'viber_number',
            ]);
        });
    }
};
