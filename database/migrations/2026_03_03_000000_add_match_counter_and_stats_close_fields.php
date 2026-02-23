<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jugadores', function (Blueprint $table): void {
            if (! Schema::hasColumn('jugadores', 'partidos_jugados')) {
                $table->unsignedBigInteger('partidos_jugados')->default(0)->after('atajadas');
            }
        });

        Schema::table('partidos', function (Blueprint $table): void {
            if (! Schema::hasColumn('partidos', 'stats_closed_at')) {
                $table->timestamp('stats_closed_at')->nullable()->after('attendance_ends_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jugadores', function (Blueprint $table): void {
            if (Schema::hasColumn('jugadores', 'partidos_jugados')) {
                $table->dropColumn('partidos_jugados');
            }
        });

        Schema::table('partidos', function (Blueprint $table): void {
            if (Schema::hasColumn('partidos', 'stats_closed_at')) {
                $table->dropColumn('stats_closed_at');
            }
        });
    }
};
