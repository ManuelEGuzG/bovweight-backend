<?php

namespace Tests\Unit;

use App\Models\Persona;
use Tests\UnitTestCase;

class PersonaCastTest extends UnitTestCase
{
    // Verifica que el campo 'activo' se convierte a boolean al asignarlo como entero
    public function test_activo_es_cast_a_boolean(): void
    {
        $persona = new Persona(['activo' => 1]);
        $this->assertIsBool($persona->activo);
        $this->assertTrue($persona->activo);
    }

    // Verifica que la columna de soft delete usa 'borrado_logico_en' en vez del default 'deleted_at'
    public function test_columna_soft_delete_es_borrado_logico_en(): void
    {
        $this->assertEquals('borrado_logico_en', Persona::DELETED_AT);
    }

    // Verifica que la llave primaria es 'cedula', de tipo string y no autoincremental
    public function test_llave_primaria_es_cedula_tipo_string(): void
    {
        $persona = new Persona();
        $this->assertEquals('cedula', $persona->getKeyName());
        $this->assertEquals('string', $persona->getKeyType());
        $this->assertFalse($persona->getIncrementing());
    }

    // Verifica que getEmailForVerification() devuelve el campo 'correo' en vez de 'email'
    public function test_get_email_for_verification_retorna_correo(): void
    {
        $persona = new Persona(['correo' => 'test@example.com']);
        $this->assertEquals('test@example.com', $persona->getEmailForVerification());
    }

    // Verifica que username() devuelve 'correo' como campo de autenticación en vez del default 'email'
    public function test_username_retorna_correo(): void
    {
        $persona = new Persona();
        $this->assertEquals('correo', $persona->username());
    }
}
