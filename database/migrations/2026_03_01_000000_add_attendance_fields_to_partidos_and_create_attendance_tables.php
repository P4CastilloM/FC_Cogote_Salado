<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partidos', function (Blueprint $table): void {
            if (! Schema::hasColumn('partidos', 'attendance_token')) {
                $table->string('attendance_token', 80)->nullable()->unique()->after('direccion');
            }

            if (! Schema::hasColumn('partidos', 'attendance_starts_at')) {
                $table->timestamp('attendance_starts_at')->nullable()->after('attendance_token');
            }

            if (! Schema::hasColumn('partidos', 'attendance_ends_at')) {
                $table->timestamp('attendance_ends_at')->nullable()->after('attendance_starts_at');
            }
        });

        if (! Schema::hasTable('partido_asistencias')) {
            Schema::create('partido_asistencias', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('partido_id');
                $table->unsignedInteger('jugador_rut');
                $table->unsignedInteger('checked_by_rut')->nullable();
                $table->timestamp('confirmed_at');
                $table->timestamps();

                $table->foreign('partido_id')->references('id')->on('partidos')->onDelete('cascade');
                $table->foreign('jugador_rut')->references('rut')->on('jugadores')->onDelete('cascade');
                $table->foreign('checked_by_rut')->references('rut')->on('jugadores')->nullOnDelete();
                $table->unique(['partido_id', 'jugador_rut']);
            });
        }

        if (! Schema::hasTable('partido_asistencia_logs')) {
            Schema::create('partido_asistencia_logs', function (Blueprint $table): void {
                $table->id();
                $table->unsignedInteger('partido_id');
                $table->unsignedInteger('actor_rut')->nullable();
                $table->unsignedInteger('target_rut');
                $table->timestamp('checked_at');
                $table->timestamps();

                $table->foreign('partido_id')->references('id')->on('partidos')->onDelete('cascade');
                $table->foreign('actor_rut')->references('rut')->on('jugadores')->nullOnDelete();
                $table->foreign('target_rut')->references('rut')->on('jugadores')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('partido_asistencia_logs');
        Schema::dropIfExists('partido_asistencias');

        Schema::table('partidos', function (Blueprint $table): void {
            if (Schema::hasColumn('partidos', 'attendance_ends_at')) {
                $table->dropColumn('attendance_ends_at');
            }
            if (Schema::hasColumn('partidos', 'attendance_starts_at')) {
                $table->dropColumn('attendance_starts_at');
            }
            if (Schema::hasColumn('partidos', 'attendance_token')) {
                $table->dropUnique('partidos_attendance_token_unique');
                $table->dropColumn('attendance_token');
            }
        });
    }
};
