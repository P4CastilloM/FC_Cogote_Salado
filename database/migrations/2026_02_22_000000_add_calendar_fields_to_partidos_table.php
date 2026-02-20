<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidos', function (Blueprint $table) {
            if (! Schema::hasColumn('partidos', 'rival')) {
                $table->string('rival', 100)->nullable()->after('nombre_lugar');
            }

            if (! Schema::hasColumn('partidos', 'hora')) {
                $table->time('hora')->nullable()->after('rival');
            }

            if (! Schema::hasColumn('partidos', 'direccion')) {
                $table->string('direccion', 180)->nullable()->after('hora');
            }
        });
    }

    public function down(): void
    {
        Schema::table('partidos', function (Blueprint $table) {
            $drop = [];

            if (Schema::hasColumn('partidos', 'direccion')) {
                $drop[] = 'direccion';
            }
            if (Schema::hasColumn('partidos', 'hora')) {
                $drop[] = 'hora';
            }
            if (Schema::hasColumn('partidos', 'rival')) {
                $drop[] = 'rival';
            }

            if (! empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
