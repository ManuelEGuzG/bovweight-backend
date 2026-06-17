<?php

namespace Tests\Feature;

use App\Models\Finca;
use Tests\TestCase;

class FincaTest extends TestCase
{
    // Verifica que el administrador puede ver todas las fincas del sistema sin restricción
    public function test_admin_ve_todas_las_fincas(): void
    {
        $this->actingAsPersona('Administrador');
        Finca::factory()->count(3)->create();

        $this->getJson('/api/v1/fincas')
            ->assertOk()
            ->assertJsonCount(3);
    }

    // Verifica que el ganadero solo ve las fincas que tiene asignadas en el pivote
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

    // Verifica que al crear una finca como ganadero, queda automáticamente registrado como dueño en el pivote
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

    // Verifica que el administrador puede crear fincas sin que se le asigne automáticamente como dueño
    public function test_admin_crea_finca_sin_auto_asignacion(): void
    {
        $admin = $this->actingAsPersona('Administrador');

        $this->postJson('/api/v1/fincas', [
            'nombre'    => 'Finca Admin',
            'ubicacion' => 'San José, CR',
        ])->assertStatus(201);

        $this->assertDatabaseMissing('finca_persona', ['cedula' => $admin->cedula]);
    }

    // Verifica que el veterinario no tiene permiso para crear fincas y recibe 403
    public function test_veterinario_no_puede_crear_finca(): void
    {
        $this->actingAsPersona('Veterinario');

        $this->postJson('/api/v1/fincas', ['nombre' => 'Finca Test'])
            ->assertForbidden();
    }

    // Verifica que el campo 'nombre' es requerido al crear una finca y falla con error de validación
    public function test_crear_finca_nombre_requerido(): void
    {
        $this->actingAsPersona('Ganadero');

        $this->postJson('/api/v1/fincas', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['nombre']);
    }

    // Verifica que el administrador puede ver el detalle de cualquier finca sin importar asignación
    public function test_admin_puede_ver_cualquier_finca(): void
    {
        $this->actingAsPersona('Administrador');
        $finca = Finca::factory()->create();

        $this->getJson("/api/v1/fincas/{$finca->id_finca}")
            ->assertOk()
            ->assertJsonFragment(['id_finca' => $finca->id_finca]);
    }

    // Verifica que el ganadero no puede ver el detalle de una finca que no tiene asignada (403)
    public function test_ganadero_no_puede_ver_finca_no_asignada(): void
    {
        $this->actingAsPersona('Ganadero');
        $finca = Finca::factory()->create();

        $this->getJson("/api/v1/fincas/{$finca->id_finca}")
            ->assertForbidden();
    }

    // Verifica que el ganadero dueño puede actualizar los datos de su finca
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

    // Verifica que el veterinario no puede actualizar datos de una finca aunque esté asignado
    public function test_veterinario_no_puede_actualizar_finca(): void
    {
        $vet = $this->actingAsPersona('Veterinario');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($vet->cedula, ['es_dueno' => false]);

        $this->putJson("/api/v1/fincas/{$finca->id_finca}", ['nombre' => 'Hack'])
            ->assertForbidden();
    }

    // Verifica que el ganadero dueño puede eliminar su finca (soft delete)
    public function test_ganadero_dueno_puede_eliminar_finca(): void
    {
        $ganadero = $this->actingAsPersona('Ganadero');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($ganadero->cedula, ['es_dueno' => true]);

        $this->deleteJson("/api/v1/fincas/{$finca->id_finca}")
            ->assertOk();

        $this->assertSoftDeleted($finca);
    }

    // Verifica que un ganadero no dueño no puede eliminar una finca y recibe 403
    public function test_ganadero_no_dueno_no_puede_eliminar_finca(): void
    {
        $ganadero = $this->actingAsPersona('Ganadero');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($ganadero->cedula, ['es_dueno' => false]);

        $this->deleteJson("/api/v1/fincas/{$finca->id_finca}")
            ->assertForbidden();
    }
}
