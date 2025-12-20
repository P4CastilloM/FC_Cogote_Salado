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
        Schema::create('jugadores', function (Blueprint $table) {

            // PK MANUAL (NO autoincremental)
            $table->unsignedInteger('rut'); // int(8)
            $table->primary('rut');

            $table->string('nombre', 25);

            $table->string('foto', 255)->nullable();

            // int(10)
            $table->unsignedBigInteger('goles')->default(0);
            $table->unsignedBigInteger('asistencia')->default(0);

            // int(3)
            $table->unsignedSmallInteger('numero_camiseta');

            // enum según MR
            $table->enum('posicion', [
                'ARQUERO',
                'DELANTERO',
                'CENTRAL',
                'DEFENSA'
            ]);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jugadores');
    }

};
