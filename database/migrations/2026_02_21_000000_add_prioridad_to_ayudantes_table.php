<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ayudantes', function (Blueprint $table): void {
            $table->unsignedTinyInteger('prioridad')
                ->default(10)
                ->after('descripcion_rol');

            $table->index('prioridad');
        });
    }

    public function down(): void
    {
        Schema::table('ayudantes', function (Blueprint $table): void {
            $table->dropIndex(['prioridad']);
            $table->dropColumn('prioridad');
        });
    }
};
