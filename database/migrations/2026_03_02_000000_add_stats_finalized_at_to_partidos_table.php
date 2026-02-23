<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidos', function (Blueprint $table): void {
            if (! Schema::hasColumn('partidos', 'stats_finalized_at')) {
                $table->timestamp('stats_finalized_at')->nullable()->after('attendance_ends_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('partidos', function (Blueprint $table): void {
            if (Schema::hasColumn('partidos', 'stats_finalized_at')) {
                $table->dropColumn('stats_finalized_at');
            }
        });
    }
};
