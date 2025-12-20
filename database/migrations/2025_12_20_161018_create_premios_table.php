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
        Schema::create('premios', function (Blueprint $table) {

            // 👇 DEBE ser BIGINT UNSIGNED
            $table->id();

            $table->unsignedBigInteger('temporada_id');

            $table->string('nombre', 20);
            $table->string('descripcion', 50)->nullable();

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
        Schema::dropIfExists('premios');
    }

};
