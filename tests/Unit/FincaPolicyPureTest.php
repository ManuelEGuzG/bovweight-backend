<?php

namespace Tests\Unit;

use App\Models\Finca;
use App\Models\Persona;
use App\Models\Rol;
use App\Policies\FincaPolicy;
use Tests\UnitTestCase;

class FincaPolicyPureTest extends UnitTestCase
{
    private FincaPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new FincaPolicy();
    }

    private function personaConRol(string $nombreRol): Persona
    {
        $persona = new Persona();
        $persona->setRelation('rol', new Rol(['nombre' => $nombreRol]));
        return $persona;
    }

    // ── viewAny ──────────────────────────────────────────────────────────────

    public function test_view_any_siempre_retorna_true(): void
    {
        foreach (['Administrador', 'Ganadero', 'Asistente', 'Veterinario'] as $rol) {
            $this->assertTrue(
                $this->policy->viewAny($this->personaConRol($rol)),
                "viewAny debería retornar true para $rol"
            );
        }
    }

    // ── view (solo admin — sin BD) ────────────────────────────────────────────

    public function test_view_admin_siempre_puede_ver_cualquier_finca(): void
    {
        $this->assertTrue($this->policy->view($this->personaConRol('Administrador'), new Finca()));
    }

    // ── create ───────────────────────────────────────────────────────────────

    public function test_create_admin_puede_crear(): void
    {
        $this->assertTrue($this->policy->create($this->personaConRol('Administrador')));
    }

    public function test_create_ganadero_puede_crear(): void
    {
        $this->assertTrue($this->policy->create($this->personaConRol('Ganadero')));
    }

    public function test_create_asistente_puede_crear(): void
    {
        $this->assertTrue($this->policy->create($this->personaConRol('Asistente')));
    }

    public function test_create_veterinario_no_puede_crear(): void
    {
        $this->assertFalse($this->policy->create($this->personaConRol('Veterinario')));
    }

    // ── update (solo admin y vet — sin BD) ───────────────────────────────────

    public function test_update_admin_siempre_puede_actualizar(): void
    {
        $this->assertTrue($this->policy->update($this->personaConRol('Administrador'), new Finca()));
    }

    public function test_update_veterinario_nunca_puede_actualizar(): void
    {
        $this->assertFalse($this->policy->update($this->personaConRol('Veterinario'), new Finca()));
    }

    // ── delete (solo admin — sin BD) ─────────────────────────────────────────

    public function test_delete_admin_siempre_puede_eliminar(): void
    {
        $this->assertTrue($this->policy->delete($this->personaConRol('Administrador'), new Finca()));
    }

    // ── restore / forceDelete ────────────────────────────────────────────────

    public function test_restore_solo_admin_puede(): void
    {
        $finca = new Finca();
        $this->assertTrue($this->policy->restore($this->personaConRol('Administrador'), $finca));
        $this->assertFalse($this->policy->restore($this->personaConRol('Ganadero'), $finca));
    }

    public function test_force_delete_solo_admin_puede(): void
    {
        $finca = new Finca();
        $this->assertTrue($this->policy->forceDelete($this->personaConRol('Administrador'), $finca));
        $this->assertFalse($this->policy->forceDelete($this->personaConRol('Veterinario'), $finca));
    }
}
