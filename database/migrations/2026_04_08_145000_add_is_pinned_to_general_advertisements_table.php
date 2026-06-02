<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_advertisements', function (Blueprint $table): void {
            $table->boolean('is_pinned')->default(false)->after('views_count');
            $table->index(['is_pinned', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('general_advertisements', function (Blueprint $table): void {
            $table->dropIndex(['is_pinned', 'is_active']);
            $table->dropColumn('is_pinned');
        });
    }
};
