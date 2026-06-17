<?php

namespace Tests\Feature;

use Tests\TestCase;

class CatalogoTest extends TestCase
{
    // Verifica que el endpoint de roles retorna exactamente 4 roles del sistema
    public function test_listar_roles(): void
    {
        $this->actingAsPersona('Ganadero');

        $this->getJson('/api/v1/roles')
            ->assertOk()
            ->assertJsonCount(4);
    }

    // Verifica que el endpoint de estados retorna exactamente 6 estados de salud animal
    public function test_listar_estados_animal(): void
    {
        $this->actingAsPersona('Ganadero');

        $this->getJson('/api/v1/estados')
            ->assertOk()
            ->assertJsonCount(6);
    }

    // Verifica que el endpoint de roles requiere autenticación y retorna 401 sin token
    public function test_roles_requiere_autenticacion(): void
    {
        $this->getJson('/api/v1/roles')->assertUnauthorized();
    }

    // Verifica que el endpoint de estados requiere autenticación y retorna 401 sin token
    public function test_estados_requiere_autenticacion(): void
    {
        $this->getJson('/api/v1/estados')->assertUnauthorized();
    }

    // Verifica que el listado de roles incluye los cuatro roles del sistema por nombre
    public function test_roles_incluye_los_cuatro_roles_del_sistema(): void
    {
        $this->actingAsPersona('Administrador');

        $response = $this->getJson('/api/v1/roles')->assertOk();
        $nombres = collect($response->json())->pluck('nombre')->toArray();

        $this->assertContains('Administrador', $nombres);
        $this->assertContains('Ganadero', $nombres);
        $this->assertContains('Asistente', $nombres);
        $this->assertContains('Veterinario', $nombres);
    }

    // Verifica que el listado de estados incluye los estados clave del sistema por nombre
    public function test_estados_incluye_todos_los_estados_del_sistema(): void
    {
        $this->actingAsPersona('Administrador');

        $response = $this->getJson('/api/v1/estados')->assertOk();
        $nombres = collect($response->json())->pluck('nombre_estado')->toArray();

        $this->assertContains('Bien', $nombres);
        $this->assertContains('Enfermo', $nombres);
        $this->assertContains('Medicado', $nombres);
        $this->assertContains('Muerto', $nombres);
    }
}
