<?php

namespace Tests\Feature;

use App\Models\Medicamento;
use Tests\TestCase;

class MedicamentoTest extends TestCase
{
    // Verifica que cualquier usuario autenticado (sin importar rol) puede listar medicamentos
    public function test_cualquier_usuario_autenticado_puede_listar_medicamentos(): void
    {
        Medicamento::factory()->count(3)->create();
        $this->actingAsPersona('Asistente');

        $this->getJson('/api/v1/medicamentos')
            ->assertOk()
            ->assertJsonCount(3);
    }

    // Verifica que el administrador puede crear un nuevo medicamento con nombre y descripción
    public function test_admin_puede_crear_medicamento(): void
    {
        $this->actingAsPersona('Administrador');

        $this->postJson('/api/v1/medicamentos', [
            'nombre'      => 'Amoxicilina 500mg',
            'descripcion' => 'Antibiótico de amplio espectro',
        ])->assertStatus(201)
          ->assertJsonFragment(['nombre' => 'Amoxicilina 500mg']);
    }

    // Verifica que el veterinario también tiene permiso para crear medicamentos
    public function test_veterinario_puede_crear_medicamento(): void
    {
        $this->actingAsPersona('Veterinario');

        $this->postJson('/api/v1/medicamentos', [
            'nombre'      => 'Ivermectina 1%',
            'descripcion' => 'Antiparasitario',
        ])->assertStatus(201);
    }

    // Verifica que el ganadero no tiene permiso para crear medicamentos y recibe 403
    public function test_ganadero_no_puede_crear_medicamento(): void
    {
        $this->actingAsPersona('Ganadero');

        $this->postJson('/api/v1/medicamentos', [
            'nombre'      => 'Test Med',
            'descripcion' => 'Test',
        ])->assertForbidden();
    }

    // Verifica que el asistente no tiene permiso para crear medicamentos y recibe 403
    public function test_asistente_no_puede_crear_medicamento(): void
    {
        $this->actingAsPersona('Asistente');

        $this->postJson('/api/v1/medicamentos', [
            'nombre' => 'Test Med',
        ])->assertForbidden();
    }

    // Verifica que no se puede crear un medicamento con un nombre que ya existe en el sistema (422)
    public function test_nombre_duplicado_retorna_422(): void
    {
        $this->actingAsPersona('Administrador');
        Medicamento::factory()->create(['nombre' => 'Penicilina']);

        $this->postJson('/api/v1/medicamentos', [
            'nombre' => 'Penicilina',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['nombre']);
    }

    // Verifica que el administrador puede eliminar un medicamento (soft delete)
    public function test_admin_puede_eliminar_medicamento(): void
    {
        $this->actingAsPersona('Administrador');
        $medicamento = Medicamento::factory()->create();

        $this->deleteJson("/api/v1/medicamentos/{$medicamento->id_medicamento}")
            ->assertOk();

        $this->assertSoftDeleted($medicamento);
    }

    // Verifica que el veterinario no puede eliminar medicamentos y recibe 403
    public function test_veterinario_no_puede_eliminar_medicamento(): void
    {
        $this->actingAsPersona('Veterinario');
        $medicamento = Medicamento::factory()->create();

        $this->deleteJson("/api/v1/medicamentos/{$medicamento->id_medicamento}")
            ->assertForbidden();
    }

    // Verifica que el ganadero no puede eliminar medicamentos y recibe 403
    public function test_ganadero_no_puede_eliminar_medicamento(): void
    {
        $this->actingAsPersona('Ganadero');
        $medicamento = Medicamento::factory()->create();

        $this->deleteJson("/api/v1/medicamentos/{$medicamento->id_medicamento}")
            ->assertForbidden();
    }

    // Verifica que el endpoint de listado retorna 401 cuando no se envía token de autenticación
    public function test_listar_medicamentos_requiere_autenticacion(): void
    {
        $this->getJson('/api/v1/medicamentos')->assertUnauthorized();
    }
}
