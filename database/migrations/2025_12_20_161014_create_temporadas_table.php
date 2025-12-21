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
        Schema::create('temporadas', function (Blueprint $table) {
            $table->id(); // id BIGINT UNSIGNED
            $table->date('fecha_inicio');
            $table->date('fecha_termino')->nullable();
            $table->string('descripcion', 150)->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporadas');
    }

};
