<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_users', function (Blueprint $table): void {
            $table->string('role', 30)->default('user')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('application_users', function (Blueprint $table): void {
            $table->dropColumn('role');
        });
    }
};
