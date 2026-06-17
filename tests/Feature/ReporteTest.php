<?php

namespace Tests\Feature;

use App\Exports\FincaReporteExport;
use App\Models\Animal;
use App\Models\Finca;
use App\Models\HistorialAnimal;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ReporteTest extends TestCase
{
    private function prepararFincaAdmin(): array
    {
        $admin = $this->actingAsPersona('Administrador');
        $finca = Finca::factory()->create();
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);
        return [$admin, $finca, $animal];
    }

    // Verifica que el endpoint de reporte retorna un JSON con la estructura correcta incluyendo totales
    public function test_resumen_finca_json(): void
    {
        [$admin, $finca, $animal] = $this->prepararFincaAdmin();

        $this->getJson("/api/v1/fincas/{$finca->id_finca}/reporte")
            ->assertOk()
            ->assertJsonStructure(['finca', 'total_animales', 'animales'])
            ->assertJsonFragment(['total_animales' => 1]);
    }

    // Verifica que el reporte de historial de un animal retorna ganancia total y el detalle de pesajes
    public function test_historial_animal_reporte_json(): void
    {
        [$admin, $finca, $animal] = $this->prepararFincaAdmin();
        $asignador = $this->crearPersona('Ganadero');
        HistorialAnimal::factory()->count(3)->create([
            'numero_arete'     => $animal->numero_arete,
            'cedula_asignador' => $asignador->cedula,
        ]);

        $this->getJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/historial-reporte"
        )->assertOk()
         ->assertJsonStructure(['animal', 'ganancia_total', 'historial']);
    }

    // Verifica que el endpoint de descarga genera y retorna el archivo Excel correctamente
    public function test_export_excel(): void
    {
        Excel::fake();
        [$admin, $finca] = $this->prepararFincaAdmin();

        $nombre = preg_replace('/[^A-Za-z0-9_-]/', '_', $finca->nombre);
        $filename = "Reporte_{$nombre}_" . date('Ymd') . '.xlsx';

        $this->get("/api/v1/fincas/{$finca->id_finca}/reporte/excel")
            ->assertOk();

        Excel::assertDownloaded($filename);
    }

    // Verifica que el endpoint de PDF retorna una respuesta con Content-Type 'application/pdf'
    public function test_export_pdf(): void
    {
        [$admin, $finca] = $this->prepararFincaAdmin();

        $response = $this->get("/api/v1/fincas/{$finca->id_finca}/reporte/pdf");

        $response->assertOk();
        $this->assertStringContainsString(
            'application/pdf',
            $response->headers->get('Content-Type')
        );
    }

    // Verifica que un ganadero no asignado a la finca recibe 403 al intentar ver el reporte
    public function test_reporte_finca_no_asignada_retorna_403(): void
    {
        $this->actingAsPersona('Ganadero');
        $finca = Finca::factory()->create();

        $this->getJson("/api/v1/fincas/{$finca->id_finca}/reporte")
            ->assertForbidden();
    }

    // Verifica que el reporte de una finca sin animales retorna total_animales en 0
    public function test_resumen_finca_sin_animales(): void
    {
        $this->actingAsPersona('Administrador');
        $finca = Finca::factory()->create();

        $this->getJson("/api/v1/fincas/{$finca->id_finca}/reporte")
            ->assertOk()
            ->assertJsonFragment(['total_animales' => 0]);
    }
}
