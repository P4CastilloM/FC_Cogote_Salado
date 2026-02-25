<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jugadores', function (Blueprint $table): void {
            if (! Schema::hasColumn('jugadores', 'apellido')) {
                $table->string('apellido', 50)->nullable()->after('nombre');
            }

            if (! Schema::hasColumn('jugadores', 'es_visitante')) {
                $table->boolean('es_visitante')->default(false)->after('partidos_jugados');
                $table->index('es_visitante');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jugadores', function (Blueprint $table): void {
            if (Schema::hasColumn('jugadores', 'es_visitante')) {
                $table->dropIndex(['es_visitante']);
                $table->dropColumn('es_visitante');
            }

            if (Schema::hasColumn('jugadores', 'apellido')) {
                $table->dropColumn('apellido');
            }
        });
    }
};
