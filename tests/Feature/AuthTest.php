<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    // Verifica que un usuario activo con credenciales correctas recibe un token de acceso
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

    // Verifica que el login falla con error 422 cuando la contraseña es incorrecta
    public function test_login_falla_con_contrasena_incorrecta(): void
    {
        $this->crearPersona('Ganadero', ['correo' => 'test@bov.com']);

        $this->postJson('/api/v1/auth/login', [
            'correo'     => 'test@bov.com',
            'contrasena' => 'incorrecta',
        ])->assertStatus(422);
    }

    // Verifica que el login falla con error 422 cuando el correo no existe en el sistema
    public function test_login_falla_con_usuario_inexistente(): void
    {
        $this->postJson('/api/v1/auth/login', [
            'correo'     => 'noexiste@bov.com',
            'contrasena' => 'password',
        ])->assertStatus(422);
    }

    // Verifica que un usuario con activo=false no puede iniciar sesión
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

    // Verifica que al hacer login se eliminan los tokens anteriores y queda solo uno activo
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

    // Verifica que el logout elimina el token actual de la base de datos
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

    // Verifica que el endpoint /me retorna los datos del usuario autenticado
    public function test_me_retorna_usuario_autenticado(): void
    {
        $persona = $this->actingAsPersona('Ganadero');

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonFragment(['cedula' => $persona->cedula]);
    }

    // Verifica que las rutas protegidas retornan 401 cuando no se envía token
    public function test_rutas_protegidas_sin_token_retornan_401(): void
    {
        $this->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->postJson('/api/v1/auth/logout')->assertUnauthorized();
    }
}
