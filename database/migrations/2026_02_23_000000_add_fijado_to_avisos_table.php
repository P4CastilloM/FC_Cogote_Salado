<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            if (! Schema::hasColumn('avisos', 'fijado')) {
                $table->boolean('fijado')->default(false)->after('foto');
                $table->index('fijado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('avisos', function (Blueprint $table) {
            if (Schema::hasColumn('avisos', 'fijado')) {
                $table->dropIndex(['fijado']);
                $table->dropColumn('fijado');
            }
        });
    }
};
