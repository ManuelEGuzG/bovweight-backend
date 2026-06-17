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

    public function test_resumen_finca_json(): void
    {
        [$admin, $finca, $animal] = $this->prepararFincaAdmin();

        $this->getJson("/api/v1/fincas/{$finca->id_finca}/reporte")
            ->assertOk()
            ->assertJsonStructure(['finca', 'total_animales', 'animales'])
            ->assertJsonFragment(['total_animales' => 1]);
    }

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

    public function test_reporte_finca_no_asignada_retorna_403(): void
    {
        $this->actingAsPersona('Ganadero');
        $finca = Finca::factory()->create();

        $this->getJson("/api/v1/fincas/{$finca->id_finca}/reporte")
            ->assertForbidden();
    }

    public function test_resumen_finca_sin_animales(): void
    {
        $this->actingAsPersona('Administrador');
        $finca = Finca::factory()->create();

        $this->getJson("/api/v1/fincas/{$finca->id_finca}/reporte")
            ->assertOk()
            ->assertJsonFragment(['total_animales' => 0]);
    }
}
