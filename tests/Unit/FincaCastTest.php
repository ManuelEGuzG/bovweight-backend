<?php

namespace Tests\Unit;

use App\Models\Finca;
use Tests\UnitTestCase;

class FincaCastTest extends UnitTestCase
{
    // Verifica que el campo 'activo' se convierte a boolean al asignarlo como entero
    public function test_activo_es_cast_a_boolean(): void
    {
        $finca = new Finca(['activo' => 1]);
        $this->assertIsBool($finca->activo);
        $this->assertTrue($finca->activo);
    }

    // Verifica que el campo 'hectareas' se convierte a float al asignarlo como string numérico
    public function test_hectareas_es_cast_a_float(): void
    {
        $finca = new Finca(['hectareas' => '150.5']);
        $this->assertIsFloat($finca->hectareas);
        $this->assertEquals(150.5, $finca->hectareas);
    }

    // Verifica que la columna de soft delete usa 'borrado_logico_en' en vez del default 'deleted_at'
    public function test_columna_soft_delete_es_borrado_logico_en(): void
    {
        $this->assertEquals('borrado_logico_en', Finca::DELETED_AT);
    }

    // Verifica que la llave primaria del modelo es 'id_finca'
    public function test_llave_primaria_es_id_finca(): void
    {
        $this->assertEquals('id_finca', (new Finca())->getKeyName());
    }
}
