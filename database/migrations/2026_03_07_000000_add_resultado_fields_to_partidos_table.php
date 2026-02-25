<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidos', function (Blueprint $table): void {
            if (! Schema::hasColumn('partidos', 'resultado_equipo_a')) {
                $table->unsignedSmallInteger('resultado_equipo_a')->nullable()->after('stats_closed_at');
            }

            if (! Schema::hasColumn('partidos', 'resultado_equipo_b')) {
                $table->unsignedSmallInteger('resultado_equipo_b')->nullable()->after('resultado_equipo_a');
            }

            if (! Schema::hasColumn('partidos', 'resultado_ganador')) {
                $table->string('resultado_ganador', 1)->nullable()->after('resultado_equipo_b');
            }

            if (! Schema::hasColumn('partidos', 'resultado_texto')) {
                $table->string('resultado_texto', 120)->nullable()->after('resultado_ganador');
            }
        });
    }

    public function down(): void
    {
        Schema::table('partidos', function (Blueprint $table): void {
            $drop = [];

            if (Schema::hasColumn('partidos', 'resultado_texto')) {
                $drop[] = 'resultado_texto';
            }
            if (Schema::hasColumn('partidos', 'resultado_ganador')) {
                $drop[] = 'resultado_ganador';
            }
            if (Schema::hasColumn('partidos', 'resultado_equipo_b')) {
                $drop[] = 'resultado_equipo_b';
            }
            if (Schema::hasColumn('partidos', 'resultado_equipo_a')) {
                $drop[] = 'resultado_equipo_a';
            }

            if (! empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
