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
        Schema::create('avisos', function (Blueprint $table) {
            $table->id(); // PK autoincremental

            $table->foreignId('temporada_id')
                ->constrained('temporadas')
                ->cascadeOnDelete();

            $table->string('titulo', 50);
            $table->string('descripcion', 120);

            $table->date('fecha');

            $table->string('foto', 255)->nullable();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avisos');
    }

};
