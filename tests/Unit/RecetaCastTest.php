<?php

namespace Tests\Unit;

use App\Models\Receta;
use Tests\UnitTestCase;

class RecetaCastTest extends UnitTestCase
{
    public function test_fecha_emision_es_cast_a_date(): void
    {
        $casts = (new Receta())->getCasts();
        $this->assertArrayHasKey('fecha_emision', $casts);
        $this->assertEquals('date', $casts['fecha_emision']);
    }

    public function test_duracion_dias_es_cast_a_integer(): void
    {
        $receta = new Receta(['duracion_dias' => '7']);
        $this->assertIsInt($receta->duracion_dias);
        $this->assertEquals(7, $receta->duracion_dias);
    }

    public function test_llave_primaria_es_id_receta(): void
    {
        $this->assertEquals('id_receta', (new Receta())->getKeyName());
    }
}
