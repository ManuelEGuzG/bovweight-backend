<?php

namespace Tests\Unit;

use App\Models\Persona;
use App\Models\Rol;
use Tests\UnitTestCase;

class PersonaRolTest extends UnitTestCase
{
    private function personaConRol(string $nombreRol): Persona
    {
        $persona = new Persona();
        $persona->setRelation('rol', new Rol(['nombre' => $nombreRol]));
        return $persona;
    }

    public function test_es_administrador_retorna_true_para_admin(): void
    {
        $this->assertTrue($this->personaConRol('Administrador')->esAdministrador());
    }

    public function test_es_administrador_retorna_false_para_ganadero(): void
    {
        $this->assertFalse($this->personaConRol('Ganadero')->esAdministrador());
    }

    public function test_es_ganadero_retorna_true(): void
    {
        $this->assertTrue($this->personaConRol('Ganadero')->esGanadero());
    }

    public function test_es_ganadero_retorna_false_para_otro_rol(): void
    {
        $this->assertFalse($this->personaConRol('Veterinario')->esGanadero());
    }

    public function test_es_asistente_retorna_true(): void
    {
        $this->assertTrue($this->personaConRol('Asistente')->esAsistente());
    }

    public function test_es_asistente_retorna_false_para_otro_rol(): void
    {
        $this->assertFalse($this->personaConRol('Ganadero')->esAsistente());
    }

    public function test_es_veterinario_retorna_true(): void
    {
        $this->assertTrue($this->personaConRol('Veterinario')->esVeterinario());
    }

    public function test_es_veterinario_retorna_false_para_otro_rol(): void
    {
        $this->assertFalse($this->personaConRol('Administrador')->esVeterinario());
    }

    public function test_tiene_rol_retorna_true_con_rol_correcto(): void
    {
        $this->assertTrue($this->personaConRol('Ganadero')->tieneRol('Ganadero'));
    }

    public function test_tiene_rol_retorna_false_con_rol_incorrecto(): void
    {
        $this->assertFalse($this->personaConRol('Ganadero')->tieneRol('Administrador'));
    }

    public function test_metodos_rol_retornan_false_sin_rol_asignado(): void
    {
        $persona = new Persona();
        $persona->setRelation('rol', null);

        $this->assertFalse($persona->esAdministrador());
        $this->assertFalse($persona->esGanadero());
        $this->assertFalse($persona->esAsistente());
        $this->assertFalse($persona->esVeterinario());
    }

    public function test_get_auth_password_retorna_contrasena(): void
    {
        $persona = new Persona(['contrasena' => 'hash_secreto']);
        $this->assertEquals('hash_secreto', $persona->getAuthPassword());
    }
}
