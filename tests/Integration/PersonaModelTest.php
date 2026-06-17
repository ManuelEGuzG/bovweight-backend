<?php

namespace Tests\Integration;

use App\Models\Persona;
use Tests\TestCase;

class PersonaModelTest extends TestCase
{
    // Verifica que el administrador obtiene los IDs de todas las fincas del sistema
    public function test_admin_obtiene_ids_de_todas_las_fincas(): void
    {
        $admin = $this->crearPersona('Administrador');
        \App\Models\Finca::factory()->count(3)->create();

        $ids = $admin->fincasAccesiblesIds();

        $this->assertCount(3, $ids);
    }

    // Verifica que el ganadero solo obtiene los IDs de las fincas que tiene asignadas en el pivote
    public function test_ganadero_obtiene_solo_ids_de_fincas_asignadas(): void
    {
        $ganadero = $this->crearPersona('Ganadero');
        $asignada = \App\Models\Finca::factory()->create();
        $asignada->personas()->attach($ganadero->cedula, ['es_dueno' => true]);
        \App\Models\Finca::factory()->create(); // no asignada

        $ids = $ganadero->fincasAccesiblesIds();

        $this->assertCount(1, $ids);
        $this->assertContains($asignada->id_finca, $ids);
    }

    // Verifica que un ganadero sin fincas asignadas recibe un array vacío
    public function test_usuario_sin_fincas_retorna_array_vacio(): void
    {
        $ganadero = $this->crearPersona('Ganadero');

        $ids = $ganadero->fincasAccesiblesIds();

        $this->assertEmpty($ids);
    }
}
