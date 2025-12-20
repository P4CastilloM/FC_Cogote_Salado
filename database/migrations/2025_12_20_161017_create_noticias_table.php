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
        Schema::create('noticias', function (Blueprint $table) {
            $table->id(); // PK autoincremental

            $table->foreignId('temporada_id')
                ->constrained('temporadas')
                ->cascadeOnDelete();

            $table->string('titulo', 60);
            $table->string('subtitulo', 100)->nullable();
            $table->text('cuerpo');

            $table->date('fecha');

            $table->string('foto', 255)->nullable();
            $table->string('foto2', 255)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noticias');
    }

};
