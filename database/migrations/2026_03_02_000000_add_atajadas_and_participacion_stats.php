<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jugador_partido', function (Blueprint $table): void {
            if (! Schema::hasColumn('jugador_partido', 'atajadas')) {
                $table->unsignedSmallInteger('atajadas')->default(0)->after('asistencias');
            }

            if (! Schema::hasColumn('jugador_partido', 'participo')) {
                $table->boolean('participo')->default(false)->after('atajadas');
            }
        });

        Schema::table('jugadores', function (Blueprint $table): void {
            if (! Schema::hasColumn('jugadores', 'atajadas')) {
                $table->unsignedBigInteger('atajadas')->default(0)->after('asistencia');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jugador_partido', function (Blueprint $table): void {
            if (Schema::hasColumn('jugador_partido', 'participo')) {
                $table->dropColumn('participo');
            }

            if (Schema::hasColumn('jugador_partido', 'atajadas')) {
                $table->dropColumn('atajadas');
            }
        });

        Schema::table('jugadores', function (Blueprint $table): void {
            if (Schema::hasColumn('jugadores', 'atajadas')) {
                $table->dropColumn('atajadas');
            }
        });
    }
};
