<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('application_users', function (Blueprint $table): void {
            $table->string('email')->nullable()->unique()->after('mobile_number');
        });

        DB::statement('ALTER TABLE application_users MODIFY mobile_number VARCHAR(20) NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE application_users SET mobile_number = CONCAT("restored_", SUBSTRING(REPLACE(id, "-", ""), 1, 12)) WHERE mobile_number IS NULL');
        DB::statement('ALTER TABLE application_users MODIFY mobile_number VARCHAR(20) NOT NULL');

        Schema::table('application_users', function (Blueprint $table): void {
            $table->dropUnique('application_users_email_unique');
            $table->dropColumn('email');
        });
    }
};
