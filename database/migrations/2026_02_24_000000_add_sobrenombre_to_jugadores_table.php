<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('jugadores', 'sobrenombre')) {
            Schema::table('jugadores', function (Blueprint $table) {
                $table->string('sobrenombre', 25)->nullable()->after('nombre');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('jugadores', 'sobrenombre')) {
            Schema::table('jugadores', function (Blueprint $table) {
                $table->dropColumn('sobrenombre');
            });
        }
    }
};
