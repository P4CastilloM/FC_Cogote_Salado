<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LineupControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_save_team_persists_equipo_ab_for_confirmed_player(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        DB::table('temporadas')->insert([
            'id' => 1,
            'fecha_inicio' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('jugadores')->insert([
            'rut' => 11111111,
            'nombre' => 'Jugador Uno',
            'goles' => 0,
            'asistencia' => 0,
            'atajadas' => 0,
            'partidos_jugados' => 0,
            'numero_camiseta' => 7,
            'posicion' => 'VOLANTE',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('partidos')->insert([
            'id' => 1,
            'fecha' => now('America/Santiago')->toDateString(),
            'hora' => now('America/Santiago')->format('H:i:s'),
            'nombre_lugar' => 'Cancha',
            'temporada_id' => 1,
            'attendance_starts_at' => now()->subHour(),
            'attendance_ends_at' => now()->addHour(),
        ]);

        DB::table('partido_asistencias')->insert([
            'partido_id' => 1,
            'jugador_rut' => 11111111,
            'confirmed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->postJson(route('admin.lineup.save-team', ['id' => 1]), [
                'jugador_rut' => 11111111,
                'equipo_ab' => 'A',
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'jugador_rut' => 11111111,
                'equipo_ab' => 'A',
            ]);

        $this->assertDatabaseHas('jugador_partido', [
            'partido_id' => 1,
            'jugador_rut' => 11111111,
            'equipo_ab' => 'A',
        ]);
    }
}
