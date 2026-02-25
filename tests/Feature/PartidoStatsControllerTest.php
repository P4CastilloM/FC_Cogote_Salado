<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PartidoStatsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_is_idempotent_with_operation_id(): void
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
            'nombre' => 'Pablo',
            'goles' => 0,
            'asistencia' => 0,
            'atajadas' => 0,
            'partidos_jugados' => 0,
            'numero_camiseta' => 9,
            'posicion' => 'DELANTERO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('partidos')->insert([
            'id' => 1,
            'fecha' => now('America/Santiago')->toDateString(),
            'hora' => now('America/Santiago')->format('H:i:s'),
            'nombre_lugar' => 'Cancha',
            'temporada_id' => 1,
        ]);

        DB::table('partido_asistencias')->insert([
            'partido_id' => 1,
            'jugador_rut' => 11111111,
            'confirmed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $payload = [
            'jugador_rut' => 11111111,
            'field' => 'goles',
            'delta' => 1,
            'operation_id' => 'op-123',
        ];

        $this->actingAs($user)
            ->postJson(route('admin.partidos.stats.update', 1), $payload)
            ->assertOk()
            ->assertJson(['ok' => true, 'applied_delta' => 1, 'value' => 1]);

        $this->actingAs($user)
            ->postJson(route('admin.partidos.stats.update', 1), $payload)
            ->assertOk()
            ->assertJson(['ok' => true, 'applied_delta' => 0, 'value' => 1]);

        $this->assertDatabaseHas('jugador_partido', [
            'partido_id' => 1,
            'jugador_rut' => 11111111,
            'goles' => 1,
        ]);
    }


    public function test_update_works_without_operation_id_for_backward_compatibility(): void
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
            'nombre' => 'Pablo',
            'goles' => 0,
            'asistencia' => 0,
            'atajadas' => 0,
            'partidos_jugados' => 0,
            'numero_camiseta' => 9,
            'posicion' => 'DELANTERO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('partidos')->insert([
            'id' => 1,
            'fecha' => now('America/Santiago')->toDateString(),
            'hora' => now('America/Santiago')->format('H:i:s'),
            'nombre_lugar' => 'Cancha',
            'temporada_id' => 1,
        ]);

        DB::table('partido_asistencias')->insert([
            'partido_id' => 1,
            'jugador_rut' => 11111111,
            'confirmed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->postJson(route('admin.partidos.stats.update', 1), [
                'jugador_rut' => 11111111,
                'field' => 'asistencias',
                'delta' => 1,
            ])
            ->assertOk()
            ->assertJson(['ok' => true, 'applied_delta' => 1, 'value' => 1]);

        $this->assertDatabaseHas('jugador_partido', [
            'partido_id' => 1,
            'jugador_rut' => 11111111,
            'asistencias' => 1,
        ]);
    }

    public function test_finish_accumulates_stats_and_matches_only_for_confirmed_players(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        DB::table('temporadas')->insert([
            'id' => 1,
            'fecha_inicio' => now()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('jugadores')->insert([
            [
                'rut' => 11111111,
                'nombre' => 'Confirmado',
                'goles' => 0,
                'asistencia' => 0,
                'atajadas' => 0,
                'partidos_jugados' => 0,
                'numero_camiseta' => 10,
                'posicion' => 'DELANTERO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rut' => 22222222,
                'nombre' => 'No confirmado',
                'goles' => 0,
                'asistencia' => 0,
                'atajadas' => 0,
                'partidos_jugados' => 0,
                'numero_camiseta' => 1,
                'posicion' => 'ARQUERO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('partidos')->insert([
            'id' => 1,
            'fecha' => now('America/Santiago')->toDateString(),
            'hora' => now('America/Santiago')->format('H:i:s'),
            'nombre_lugar' => 'Cancha',
            'temporada_id' => 1,
        ]);

        DB::table('partido_asistencias')->insert([
            'partido_id' => 1,
            'jugador_rut' => 11111111,
            'confirmed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('jugador_partido')->insert([
            [
                'partido_id' => 1,
                'jugador_rut' => 11111111,
                'goles' => 2,
                'asistencias' => 1,
                'atajadas' => 0,
                'participo' => true,
                'equipo_ab' => 'A',
            ],
            [
                'partido_id' => 1,
                'jugador_rut' => 22222222,
                'goles' => 9,
                'asistencias' => 9,
                'atajadas' => 9,
                'participo' => true,
                'equipo_ab' => 'B',
            ],
        ]);

        $this->actingAs($user)
            ->post(route('admin.partidos.stats.finish', 1))
            ->assertRedirect(route('admin.partidos.stats', 1));

        $this->assertDatabaseHas('jugadores', [
            'rut' => 11111111,
            'goles' => 2,
            'asistencia' => 1,
            'partidos_jugados' => 1,
        ]);

        $this->assertDatabaseHas('jugadores', [
            'rut' => 22222222,
            'goles' => 0,
            'asistencia' => 0,
            'partidos_jugados' => 0,
        ]);


        $this->assertDatabaseHas('partidos', [
            'id' => 1,
            'resultado_equipo_a' => 2,
            'resultado_equipo_b' => 0,
            'resultado_ganador' => 'A',
            'resultado_texto' => '2 - 0 · Ganó Equipo A',
        ]);
    }
}
