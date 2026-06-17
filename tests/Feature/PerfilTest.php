<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    public function test_ver_perfil_propio(): void
    {
        $persona = $this->actingAsPersona('Ganadero');

        $this->getJson('/api/v1/perfil')
            ->assertOk()
            ->assertJsonFragment(['cedula' => $persona->cedula]);
    }

    public function test_actualizar_nombre(): void
    {
        $this->actingAsPersona('Ganadero');

        $this->putJson('/api/v1/perfil', ['nombre' => 'NuevoNombre'])
            ->assertOk()
            ->assertJsonPath('persona.nombre', 'NuevoNombre');
    }

    public function test_actualizar_correo(): void
    {
        $this->actingAsPersona('Ganadero');

        $this->putJson('/api/v1/perfil', ['correo' => 'nuevo@correo.com'])
            ->assertOk()
            ->assertJsonPath('persona.correo', 'nuevo@correo.com');
    }

    public function test_cambiar_contrasena_exitoso(): void
    {
        $this->actingAsPersona('Ganadero', ['contrasena' => Hash::make('actual123')]);

        $this->putJson('/api/v1/perfil', [
            'contrasena_actual'       => 'actual123',
            'contrasena'              => 'nueva12345',
            'contrasena_confirmation' => 'nueva12345',
        ])->assertOk();
    }

    public function test_cambiar_contrasena_falla_si_actual_es_incorrecta(): void
    {
        $this->actingAsPersona('Ganadero', ['contrasena' => Hash::make('actual123')]);

        $this->putJson('/api/v1/perfil', [
            'contrasena_actual'       => 'incorrecta',
            'contrasena'              => 'nueva12345',
            'contrasena_confirmation' => 'nueva12345',
        ])->assertStatus(422);
    }

    public function test_cambiar_contrasena_falla_sin_confirmation(): void
    {
        $this->actingAsPersona('Ganadero', ['contrasena' => Hash::make('actual123')]);

        $this->putJson('/api/v1/perfil', [
            'contrasena_actual' => 'actual123',
            'contrasena'        => 'nueva12345',
        ])->assertStatus(422);
    }

    public function test_perfil_requiere_autenticacion(): void
    {
        $this->getJson('/api/v1/perfil')->assertUnauthorized();
        $this->putJson('/api/v1/perfil', ['nombre' => 'Test'])->assertUnauthorized();
    }
}
