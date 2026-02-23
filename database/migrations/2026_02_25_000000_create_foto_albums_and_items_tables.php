<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foto_albums', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 90);
            $table->timestamps();
        });

        Schema::create('foto_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('album_id')->nullable()->constrained('foto_albums')->nullOnDelete();
            $table->string('path', 255);
            $table->timestamps();
            $table->index(['album_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foto_items');
        Schema::dropIfExists('foto_albums');
    }
};
