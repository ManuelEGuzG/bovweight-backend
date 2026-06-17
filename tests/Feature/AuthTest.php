<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_login_exitoso_retorna_token(): void
    {
        $this->crearPersona('Ganadero', [
            'correo'     => 'test@bov.com',
            'contrasena' => Hash::make('secret123'),
        ]);

        $this->postJson('/api/v1/auth/login', [
            'correo'     => 'test@bov.com',
            'contrasena' => 'secret123',
        ])->assertOk()
          ->assertJsonStructure(['message', 'persona', 'token']);
    }

    public function test_login_falla_con_contrasena_incorrecta(): void
    {
        $this->crearPersona('Ganadero', ['correo' => 'test@bov.com']);

        $this->postJson('/api/v1/auth/login', [
            'correo'     => 'test@bov.com',
            'contrasena' => 'incorrecta',
        ])->assertStatus(422);
    }

    public function test_login_falla_con_usuario_inexistente(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'correo'     => 'noexiste@bov.com',
            'contrasena' => 'password',
        ])->assertStatus(422);
    }

    public function test_login_falla_con_usuario_inactivo(): void
    {
        $this->crearPersona('Ganadero', [
            'correo'     => 'inactivo@bov.com',
            'contrasena' => Hash::make('password'),
            'activo'     => false,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'correo'     => 'inactivo@bov.com',
            'contrasena' => 'password',
        ])->assertStatus(422);
    }

    public function test_login_revoca_tokens_anteriores(): void
    {
        $persona = $this->crearPersona('Ganadero', [
            'correo'     => 'test@bov.com',
            'contrasena' => Hash::make('password'),
        ]);
        $persona->createToken('token-1');
        $persona->createToken('token-2');
        $this->assertDatabaseCount('personal_access_tokens', 2);

        $this->postJson('/api/v1/auth/login', [
            'correo'     => 'test@bov.com',
            'contrasena' => 'password',
        ])->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_logout_elimina_token_actual(): void
    {
        $persona = $this->crearPersona('Ganadero');
        $token = $persona->createToken('test-token')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJson(['message' => 'Sesión cerrada correctamente.']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_me_retorna_usuario_autenticado(): void
    {
        $persona = $this->actingAsPersona('Ganadero');

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonFragment(['cedula' => $persona->cedula]);
    }

    public function test_rutas_protegidas_sin_token_retornan_401(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
    }
}
