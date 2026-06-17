<?php

namespace Tests\Unit;

use App\Models\Finca;
use Tests\UnitTestCase;

class FincaCastTest extends UnitTestCase
{
    public function test_activo_es_cast_a_boolean(): void
    {
        $finca = new Finca(['activo' => 1]);
        $this->assertIsBool($finca->activo);
        $this->assertTrue($finca->activo);
    }

    public function test_hectareas_es_cast_a_float(): void
    {
        $finca = new Finca(['hectareas' => '150.5']);
        $this->assertIsFloat($finca->hectareas);
        $this->assertEquals(150.5, $finca->hectareas);
    }

    public function test_columna_soft_delete_es_borrado_logico_en(): void
    {
        $this->assertEquals('borrado_logico_en', Finca::DELETED_AT);
    }

    public function test_llave_primaria_es_id_finca(): void
    {
        $this->assertEquals('id_finca', (new Finca())->getKeyName());
    }
}
