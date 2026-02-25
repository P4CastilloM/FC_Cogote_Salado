<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jugador_partido', function (Blueprint $table): void {
            if (! Schema::hasColumn('jugador_partido', 'equipo_ab')) {
                $table->string('equipo_ab', 1)->nullable()->after('participo');
                $table->index(['partido_id', 'equipo_ab'], 'jugador_partido_partido_equipo_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jugador_partido', function (Blueprint $table): void {
            if (Schema::hasColumn('jugador_partido', 'equipo_ab')) {
                $table->dropIndex('jugador_partido_partido_equipo_idx');
                $table->dropColumn('equipo_ab');
            }
        });
    }
};
