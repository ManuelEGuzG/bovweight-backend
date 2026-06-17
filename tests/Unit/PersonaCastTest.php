<?php

namespace Tests\Unit;

use App\Models\Persona;
use Tests\UnitTestCase;

class PersonaCastTest extends UnitTestCase
{
    public function test_activo_es_cast_a_boolean(): void
    {
        $persona = new Persona(['activo' => 1]);
        $this->assertIsBool($persona->activo);
        $this->assertTrue($persona->activo);
    }

    public function test_columna_soft_delete_es_borrado_logico_en(): void
    {
        $this->assertEquals('borrado_logico_en', Persona::DELETED_AT);
    }

    public function test_llave_primaria_es_cedula_tipo_string(): void
    {
        $persona = new Persona();
        $this->assertEquals('cedula', $persona->getKeyName());
        $this->assertEquals('string', $persona->getKeyType());
        $this->assertFalse($persona->getIncrementing());
    }

    public function test_get_email_for_verification_retorna_correo(): void
    {
        $persona = new Persona(['correo' => 'test@example.com']);
        $this->assertEquals('test@example.com', $persona->getEmailForVerification());
    }

    public function test_username_retorna_correo(): void
    {
        $persona = new Persona();
        $this->assertEquals('correo', $persona->username());
    }
}
