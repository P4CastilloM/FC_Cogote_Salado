<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PartidoStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_and_finalize_match_stats_only_for_confirmed_players(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'ayudante',
        ]);

        $this->actingAs($user);

        $temporadaId = DB::table('temporadas')->insertGetId([
            'fecha_inicio' => Carbon::now()->startOfYear()->toDateString(),
            'fecha_termino' => Carbon::now()->endOfYear()->toDateString(),
            'descripcion' => 'Temporada test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kickoff = now()->subHour();

        $partidoId = DB::table('partidos')->insertGetId([
            'fecha' => $kickoff->toDateString(),
            'hora' => $kickoff->format('H:i'),
            'rival' => 'Rival Test',
            'nombre_lugar' => 'Cancha Test',
            'direccion' => 'Dirección Test',
            'temporada_id' => $temporadaId,
            'attendance_token' => 'token-test-123',
            'attendance_starts_at' => now()->subDays(2),
            'attendance_ends_at' => now()->addDays(2),
        ]);

        DB::table('jugadores')->insert([
            [
                'rut' => 11111111,
                'nombre' => 'Confirmado',
                'goles' => 0,
                'asistencia' => 0,
                'numero_camiseta' => 10,
                'posicion' => 'DELANTERO',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rut' => 22222222,
                'nombre' => 'No Confirmado',
                'goles' => 0,
                'asistencia' => 0,
                'numero_camiseta' => 8,
                'posicion' => 'CENTRAL',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('partido_asistencias')->insert([
            'partido_id' => $partidoId,
            'jugador_rut' => 11111111,
            'checked_by_rut' => 11111111,
            'confirmed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $syncResponse = $this->postJson(route('admin.partidos.stats.sync', $partidoId), [
            'entries' => [
                ['jugador_rut' => 11111111, 'goles' => 2, 'asistencias' => 1],
            ],
        ]);

        $syncResponse->assertOk()->assertJsonPath('ok', true);

        $this->postJson(route('admin.partidos.finalize', $partidoId))
            ->assertOk()
            ->assertJsonPath('ok', true);

        $confirmed = DB::table('jugadores')->where('rut', 11111111)->first();
        $notConfirmed = DB::table('jugadores')->where('rut', 22222222)->first();

        $this->assertSame(2, (int) $confirmed->goles);
        $this->assertSame(1, (int) $confirmed->asistencia);
        $this->assertSame(0, (int) $notConfirmed->goles);
        $this->assertSame(0, (int) $notConfirmed->asistencia);

        $this->postJson(route('admin.partidos.finalize', $partidoId))
            ->assertStatus(422);
    }
}
