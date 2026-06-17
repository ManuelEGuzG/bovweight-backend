<?php

namespace Tests\Integration;

use App\Models\Finca;
use App\Models\Persona;
use App\Policies\FincaPolicy;
use Tests\TestCase;

class FincaPolicyTest extends TestCase
{
    private FincaPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new FincaPolicy();
    }

    // ── view ─────────────────────────────────────────────────────────────────

    // Verifica que un ganadero registrado en el pivote de la finca puede verla
    public function test_view_ganadero_asignado_puede_ver_finca(): void
    {
        $ganadero = $this->crearPersona('Ganadero');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($ganadero->cedula, ['es_dueno' => true]);

        $this->assertTrue($this->policy->view($ganadero, $finca));
    }

    // Verifica que un ganadero no registrado en el pivote no puede ver la finca
    public function test_view_ganadero_no_asignado_no_puede_ver_finca(): void
    {
        $ganadero = $this->crearPersona('Ganadero');
        $finca = Finca::factory()->create();

        $this->assertFalse($this->policy->view($ganadero, $finca));
    }

    // ── update ───────────────────────────────────────────────────────────────

    // Verifica que un ganadero asignado al pivote puede actualizar la finca
    public function test_update_ganadero_asignado_puede_actualizar(): void
    {
        $ganadero = $this->crearPersona('Ganadero');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($ganadero->cedula, ['es_dueno' => true]);

        $this->assertTrue($this->policy->update($ganadero, $finca));
    }

    // Verifica que un ganadero no asignado no puede actualizar la finca
    public function test_update_ganadero_no_asignado_no_puede_actualizar(): void
    {
        $ganadero = $this->crearPersona('Ganadero');
        $finca = Finca::factory()->create();

        $this->assertFalse($this->policy->update($ganadero, $finca));
    }

    // ── delete ───────────────────────────────────────────────────────────────

    // Verifica que un ganadero marcado como dueño en el pivote puede eliminar la finca
    public function test_delete_ganadero_dueno_puede_eliminar(): void
    {
        $ganadero = $this->crearPersona('Ganadero');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($ganadero->cedula, ['es_dueno' => true]);

        $this->assertTrue($this->policy->delete($ganadero, $finca));
    }

    // Verifica que un ganadero asignado pero no dueño no puede eliminar la finca
    public function test_delete_ganadero_no_dueno_no_puede_eliminar(): void
    {
        $ganadero = $this->crearPersona('Ganadero');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($ganadero->cedula, ['es_dueno' => false]);

        $this->assertFalse($this->policy->delete($ganadero, $finca));
    }

    // Verifica que un asistente asignado tampoco puede eliminar la finca aunque esté en el pivote
    public function test_delete_asistente_no_puede_eliminar(): void
    {
        $asistente = $this->crearPersona('Asistente');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($asistente->cedula, ['es_dueno' => false]);

        $this->assertFalse($this->policy->delete($asistente, $finca));
    }
}
