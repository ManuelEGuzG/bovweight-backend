<?php

namespace Tests\Unit;

use App\Models\Animal;
use Tests\UnitTestCase;

class AnimalCastTest extends UnitTestCase
{
    public function test_activo_es_cast_a_boolean(): void
    {
        $animal = new Animal(['activo' => 0]);
        $this->assertIsBool($animal->activo);
        $this->assertFalse($animal->activo);
    }

    public function test_fecha_nacimiento_es_cast_a_date(): void
    {
        $casts = (new Animal())->getCasts();
        $this->assertArrayHasKey('fecha_nacimiento', $casts);
        $this->assertEquals('date', $casts['fecha_nacimiento']);
    }

    public function test_columna_soft_delete_es_borrado_logico_en(): void
    {
        $this->assertEquals('borrado_logico_en', Animal::DELETED_AT);
    }

    public function test_llave_primaria_es_numero_arete_tipo_string(): void
    {
        $animal = new Animal();
        $this->assertEquals('numero_arete', $animal->getKeyName());
        $this->assertEquals('string', $animal->getKeyType());
        $this->assertFalse($animal->getIncrementing());
    }
}
