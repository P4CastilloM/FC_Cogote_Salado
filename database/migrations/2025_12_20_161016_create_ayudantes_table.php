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
        Schema::create('ayudantes', function (Blueprint $table) {
            $table->id(); // PK autoincremental

            $table->string('nombre', 20);
            $table->string('apellido', 20)->nullable();
            $table->string('descripcion_rol', 50)->nullable();

            $table->string('foto', 255)->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayudantes');
    }

};
