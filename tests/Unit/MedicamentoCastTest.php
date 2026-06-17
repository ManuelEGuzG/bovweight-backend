<?php

namespace Tests\Unit;

use App\Models\Medicamento;
use Tests\UnitTestCase;

class MedicamentoCastTest extends UnitTestCase
{
    // Verifica que la columna de soft delete usa 'borrado_logico_en' en vez del default 'deleted_at'
    public function test_columna_soft_delete_es_borrado_logico_en(): void
    {
        $this->assertEquals('borrado_logico_en', Medicamento::DELETED_AT);
    }

    // Verifica que 'nombre' y 'descripcion' están en $fillable y se pueden asignar masivamente
    public function test_nombre_y_descripcion_son_fillable(): void
    {
        $medicamento = new Medicamento([
            'nombre'      => 'Amoxicilina',
            'descripcion' => 'Antibiótico de amplio espectro',
        ]);

        $this->assertEquals('Amoxicilina', $medicamento->nombre);
        $this->assertEquals('Antibiótico de amplio espectro', $medicamento->descripcion);
    }

    // Verifica que la llave primaria del modelo es 'id_medicamento'
    public function test_llave_primaria_es_id_medicamento(): void
    {
        $this->assertEquals('id_medicamento', (new Medicamento())->getKeyName());
    }
}
