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
        Schema::create('premio_jugador', function (Blueprint $table) {

            // FK a premios.id (BIGINT)
            $table->unsignedBigInteger('premio_id');

            // FK a jugadores.rut (INT, NO id)
            $table->unsignedInteger('jugador_rut');

            $table->date('fecha_otorgado');

            $table->primary(['premio_id', 'jugador_rut']);

            $table->foreign('premio_id')
                ->references('id')
                ->on('premios')
                ->onDelete('cascade');

            $table->foreign('jugador_rut')
                ->references('rut')
                ->on('jugadores')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premio_jugador');
    }

};
