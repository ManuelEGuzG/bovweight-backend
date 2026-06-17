<?php

namespace Tests\Integration;

use App\Models\Medicamento;
use Tests\TestCase;

class MedicamentoModelTest extends TestCase
{
    // Verifica que un medicamento eliminado no aparece en consultas normales pero sí con withTrashed()
    public function test_medicamento_eliminado_no_aparece_en_queries_normales(): void
    {
        $medicamento = Medicamento::factory()->create();
        $id = $medicamento->id_medicamento;
        $medicamento->delete();

        $this->assertNull(Medicamento::find($id));
        $this->assertNotNull(Medicamento::withTrashed()->find($id));
    }

    // Verifica que un medicamento eliminado puede restaurarse y vuelve a aparecer en consultas normales
    public function test_medicamento_restaurado_vuelve_a_aparecer(): void
    {
        $medicamento = Medicamento::factory()->create();
        $id = $medicamento->id_medicamento;
        $medicamento->delete();

        Medicamento::withTrashed()->find($id)->restore();

        $this->assertNotNull(Medicamento::find($id));
    }
}
