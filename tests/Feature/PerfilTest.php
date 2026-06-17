<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PerfilTest extends TestCase
{
    // Verifica que el usuario autenticado puede consultar sus propios datos de perfil
    public function test_ver_perfil_propio(): void
    {
        $persona = $this->actingAsPersona('Ganadero');

        $this->getJson('/api/v1/perfil')
            ->assertOk()
            ->assertJsonFragment(['cedula' => $persona->cedula]);
    }

    // Verifica que el usuario puede actualizar su nombre desde el endpoint de perfil
    public function test_actualizar_nombre(): void
    {
        $this->actingAsPersona('Ganadero');

        $this->putJson('/api/v1/perfil', ['nombre' => 'NuevoNombre'])
            ->assertOk()
            ->assertJsonPath('persona.nombre', 'NuevoNombre');
    }

    // Verifica que el usuario puede actualizar su correo electrónico desde el endpoint de perfil
    public function test_actualizar_correo(): void
    {
        $this->actingAsPersona('Ganadero');

        $this->putJson('/api/v1/perfil', ['correo' => 'nuevo@correo.com'])
            ->assertOk()
            ->assertJsonPath('persona.correo', 'nuevo@correo.com');
    }

    // Verifica que el cambio de contraseña funciona correctamente cuando se envía la contraseña actual válida
    public function test_cambiar_contrasena_exitoso(): void
    {
        $this->actingAsPersona('Ganadero', ['contrasena' => Hash::make('actual123')]);

        $this->putJson('/api/v1/perfil', [
            'contrasena_actual'       => 'actual123',
            'contrasena'              => 'nueva12345',
            'contrasena_confirmation' => 'nueva12345',
        ])->assertOk();
    }

    // Verifica que el cambio de contraseña falla con 422 si la contraseña actual enviada es incorrecta
    public function test_cambiar_contrasena_falla_si_actual_es_incorrecta(): void
    {
        $this->actingAsPersona('Ganadero', ['contrasena' => Hash::make('actual123')]);

        $this->putJson('/api/v1/perfil', [
            'contrasena_actual'       => 'incorrecta',
            'contrasena'              => 'nueva12345',
            'contrasena_confirmation' => 'nueva12345',
        ])->assertStatus(422);
    }

    // Verifica que el cambio de contraseña falla con 422 si no se envía la confirmación de contraseña
    public function test_cambiar_contrasena_falla_sin_confirmation(): void
    {
        $this->actingAsPersona('Ganadero', ['contrasena' => Hash::make('actual123')]);

        $this->putJson('/api/v1/perfil', [
            'contrasena_actual' => 'actual123',
            'contrasena'        => 'nueva12345',
        ])->assertStatus(422);
    }

    // Verifica que los endpoints de perfil retornan 401 cuando no hay token de autenticación
    public function test_perfil_requiere_autenticacion(): void
    {
        $this->getJson('/api/v1/perfil')->assertUnauthorized();
        $this->putJson('/api/v1/perfil', ['nombre' => 'Test'])->assertUnauthorized();
    }
}
