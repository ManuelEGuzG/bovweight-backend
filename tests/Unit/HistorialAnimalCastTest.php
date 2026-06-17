<?php

namespace Tests\Unit;

use App\Models\HistorialAnimal;
use Tests\UnitTestCase;

class HistorialAnimalCastTest extends UnitTestCase
{
    // Verifica que el campo 'peso' se convierte a float al asignarlo como string numérico
    public function test_peso_es_cast_a_float(): void
    {
        $historial = new HistorialAnimal(['peso' => '350']);
        $this->assertIsFloat($historial->peso);
        $this->assertEquals(350.0, $historial->peso);
    }

    // Verifica que el campo 'peso_real' se convierte a float al asignarlo como string numérico
    public function test_peso_real_es_cast_a_float(): void
    {
        $historial = new HistorialAnimal(['peso_real' => '320.5']);
        $this->assertIsFloat($historial->peso_real);
    }

    // Verifica que el campo 'confianza' se convierte a float al asignarlo como string numérico
    public function test_confianza_es_cast_a_float(): void
    {
        $historial = new HistorialAnimal(['confianza' => '0.92']);
        $this->assertIsFloat($historial->confianza);
        $this->assertEquals(0.92, $historial->confianza);
    }

    // Verifica que el campo 'caja_deteccion' almacena y devuelve un array (serializado como JSON)
    public function test_caja_deteccion_es_cast_a_array(): void
    {
        $caja = [10, 20, 300, 400];
        $historial = new HistorialAnimal(['caja_deteccion' => $caja]);
        $this->assertIsArray($historial->caja_deteccion);
        $this->assertEquals($caja, $historial->caja_deteccion);
    }

    // Verifica que el modelo tiene definido el cast datetime para 'fecha_de_foto'
    public function test_fecha_de_foto_es_cast_a_datetime(): void
    {
        $casts = (new HistorialAnimal())->getCasts();
        $this->assertArrayHasKey('fecha_de_foto', $casts);
        $this->assertEquals('datetime', $casts['fecha_de_foto']);
    }

    // Verifica que la llave primaria del modelo es 'id_historial'
    public function test_llave_primaria_es_id_historial(): void
    {
        $this->assertEquals('id_historial', (new HistorialAnimal())->getKeyName());
    }
}
