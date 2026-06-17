<?php

namespace Tests\Integration;

use App\Models\Medicamento;
use Tests\TestCase;

class MedicamentoModelTest extends TestCase
{
    public function test_medicamento_eliminado_no_aparece_en_queries_normales(): void
    {
        $medicamento = Medicamento::factory()->create();
        $id = $medicamento->id_medicamento;
        $medicamento->delete();

        $this->assertNull(Medicamento::find($id));
        $this->assertNotNull(Medicamento::withTrashed()->find($id));
    }

    public function test_medicamento_restaurado_vuelve_a_aparecer(): void
    {
        $medicamento = Medicamento::factory()->create();
        $id = $medicamento->id_medicamento;
        $medicamento->delete();

        Medicamento::withTrashed()->find($id)->restore();

        $this->assertNotNull(Medicamento::find($id));
    }
}
