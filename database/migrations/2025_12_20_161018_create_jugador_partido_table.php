<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jugador_partido', function (Blueprint $table) {

            // FK a jugador.rut
            $table->unsignedInteger('jugador_rut');

            // FK a partido.id
            $table->unsignedInteger('partido_id');

            // 👇 goles y asistencias DEL PARTIDO
            $table->unsignedSmallInteger('goles')->default(0);
            $table->unsignedSmallInteger('asistencias')->default(0);

            $table->primary(['jugador_rut', 'partido_id']);

            $table->foreign('jugador_rut')
                ->references('rut')
                ->on('jugadores')
                ->onDelete('cascade');

            $table->foreign('partido_id')
                ->references('id')
                ->on('partidos')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jugador_partido');
    }

};
