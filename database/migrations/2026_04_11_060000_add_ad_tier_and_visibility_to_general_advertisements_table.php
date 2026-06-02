<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('general_advertisements', function (Blueprint $table): void {
            $table->string('ad_tier', 20)->default('normal')->after('status');
            $table->timestamp('top_until')->nullable()->after('ad_tier');
            $table->timestamp('expires_at')->nullable()->after('top_until');
            $table->index('ad_tier');
            $table->index('top_until');
            $table->index('expires_at');
        });

        $existing = DB::table('general_advertisements')
            ->whereNull('expires_at')
            ->get(['id', 'created_at']);

        foreach ($existing as $row) {
            $created = $row->created_at ? Carbon::parse($row->created_at) : now();
            DB::table('general_advertisements')
                ->where('id', $row->id)
                ->update([
                    'ad_tier' => 'normal',
                    'expires_at' => $created->addDays(2),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('general_advertisements', function (Blueprint $table): void {
            $table->dropIndex(['ad_tier']);
            $table->dropIndex(['top_until']);
            $table->dropIndex(['expires_at']);
            $table->dropColumn(['ad_tier', 'top_until', 'expires_at']);
        });
    }
};
