<?php

namespace Tests\Feature;

use Tests\TestCase;

class ValidacionLoginTest extends TestCase
{
    public function test_login_falla_sin_correo()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'contrasena' => 'password'
        ]);

        $response->assertStatus(422);
    }

    public function test_login_falla_sin_contrasena()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'correo' => 'admin@bovweight.com'
        ]);

        $response->assertStatus(422);
    }

    public function test_login_falla_con_correo_invalido()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'correo' => 'correo-invalido',
            'contrasena' => 'password'
        ]);

        $response->assertStatus(422);
    }
}
