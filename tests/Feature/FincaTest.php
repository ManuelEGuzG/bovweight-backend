<?php

namespace Tests\Feature;

use App\Models\Finca;
use Tests\TestCase;

class FincaTest extends TestCase
{
    public function test_admin_ve_todas_las_fincas(): void
    {
        $this->actingAsPersona('Administrador');
        Finca::factory()->count(3)->create();

        $this->getJson('/api/v1/fincas')
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_ganadero_solo_ve_fincas_asignadas(): void
    {
        $ganadero = $this->actingAsPersona('Ganadero');
        $asignada = Finca::factory()->create();
        $asignada->personas()->attach($ganadero->cedula, ['es_dueno' => true]);
        Finca::factory()->create(); // no asignada

        $this->getJson('/api/v1/fincas')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_ganadero_queda_como_dueno_al_crear_finca(): void
    {
        $ganadero = $this->actingAsPersona('Ganadero');

        $response = $this->postJson('/api/v1/fincas', [
            'nombre'    => 'Finca El Cielo',
            'ubicacion' => 'Cartago, CR',
        ])->assertStatus(201);

        $this->assertDatabaseHas('finca_persona', [
            'cedula'   => $ganadero->cedula,
            'es_dueno' => true,
        ]);
    }

    public function test_admin_crea_finca_sin_auto_asignacion(): void
    {
        $admin = $this->actingAsPersona('Administrador');

        $this->postJson('/api/v1/fincas', [
            'nombre'    => 'Finca Admin',
            'ubicacion' => 'San José, CR',
        ])->assertStatus(201);

        $this->assertDatabaseMissing('finca_persona', ['cedula' => $admin->cedula]);
    }

    public function test_veterinario_no_puede_crear_finca(): void
    {
        $this->actingAsPersona('Veterinario');

        $this->postJson('/api/v1/fincas', ['nombre' => 'Finca Test'])
            ->assertForbidden();
    }

    public function test_crear_finca_nombre_requerido(): void
    {
        $this->actingAsPersona('Ganadero');

        $this->postJson('/api/v1/fincas', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    public function test_admin_puede_ver_cualquier_finca(): void
    {
        $this->actingAsPersona('Administrador');
        $finca = Finca::factory()->create();

        $this->getJson("/api/v1/fincas/{$finca->id_finca}")
            ->assertOk()
            ->assertJsonFragment(['id_finca' => $finca->id_finca]);
    }

    public function test_ganadero_no_puede_ver_finca_no_asignada(): void
    {
        $this->actingAsPersona('Ganadero');
        $finca = Finca::factory()->create();

        $this->getJson("/api/v1/fincas/{$finca->id_finca}")
            ->assertForbidden();
    }

    public function test_actualizar_finca_propia(): void
    {
        $ganadero = $this->actingAsPersona('Ganadero');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($ganadero->cedula, ['es_dueno' => true]);

        $this->putJson("/api/v1/fincas/{$finca->id_finca}", [
            'nombre' => 'Nombre Actualizado',
        ])->assertOk()
          ->assertJsonFragment(['nombre' => 'Nombre Actualizado']);
    }

    public function test_veterinario_no_puede_actualizar_finca(): void
    {
        $vet = $this->actingAsPersona('Veterinario');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($vet->cedula, ['es_dueno' => false]);

        $this->putJson("/api/v1/fincas/{$finca->id_finca}", ['nombre' => 'Hack'])
            ->assertForbidden();
    }

    public function test_ganadero_dueno_puede_eliminar_finca(): void
    {
        $ganadero = $this->actingAsPersona('Ganadero');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($ganadero->cedula, ['es_dueno' => true]);

        $this->deleteJson("/api/v1/fincas/{$finca->id_finca}")
            ->assertOk();

        $this->assertSoftDeleted($finca);
    }

    public function test_ganadero_no_dueno_no_puede_eliminar_finca(): void
    {
        $ganadero = $this->actingAsPersona('Ganadero');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($ganadero->cedula, ['es_dueno' => false]);

        $this->deleteJson("/api/v1/fincas/{$finca->id_finca}")
            ->assertForbidden();
    }
}
