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
        Schema::create('partidos', function (Blueprint $table) {

            $table->increments('id'); // id int (según tu MR)

            $table->date('fecha');

            $table->string('nombre_lugar', 100);

            // 👇 DEBE ser BIGINT UNSIGNED porque temporadas.id lo es
            $table->unsignedBigInteger('temporada_id');

            $table->foreign('temporada_id')
                ->references('id')
                ->on('temporadas')
                ->onDelete('cascade');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partidos');
    }

};
