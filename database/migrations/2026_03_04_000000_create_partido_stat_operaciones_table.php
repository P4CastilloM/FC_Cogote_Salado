<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('partido_stat_operaciones')) {
            return;
        }

        Schema::create('partido_stat_operaciones', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('partido_id');
            $table->string('operation_id', 80);
            $table->unsignedInteger('jugador_rut');
            $table->string('field', 20);
            $table->smallInteger('delta');
            $table->timestamp('created_at');

            $table->foreign('partido_id')->references('id')->on('partidos')->onDelete('cascade');
            $table->foreign('jugador_rut')->references('rut')->on('jugadores')->onDelete('cascade');
            $table->unique(['partido_id', 'operation_id']);
            $table->index(['partido_id', 'jugador_rut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partido_stat_operaciones');
    }
};
