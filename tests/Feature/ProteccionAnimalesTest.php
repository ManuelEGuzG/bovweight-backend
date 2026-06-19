<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProteccionAnimalesTest extends TestCase
{
    public function test_listar_animales_requiere_autenticacion()
    {
        $response = $this->getJson('/api/v1/fincas/1/animales');

        $response->assertStatus(401);
    }

    public function test_crear_animal_requiere_autenticacion()
    {
        $response = $this->postJson('/api/v1/fincas/1/animales', [
            'nombre' => 'Toro Prueba',
            'numero_arete' => 'A-001'
        ]);

        $response->assertStatus(401);
    }
}
