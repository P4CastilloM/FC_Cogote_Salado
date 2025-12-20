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
        Schema::create('ayudante_temporada', function (Blueprint $table) {
            $table->foreignId('ayudante_id')
                ->constrained('ayudantes')
                ->cascadeOnDelete();

            $table->foreignId('temporada_id')
                ->constrained('temporadas')
                ->cascadeOnDelete();

            // opcional, por si después quieres distinguir roles por temporada
            $table->string('rol_en_temporada', 50)->nullable();

            $table->timestamps();

            $table->primary(['ayudante_id', 'temporada_id']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayudante_temporada');
    }

};
