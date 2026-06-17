<?php

namespace Tests\Integration;

use App\Models\Animal;
use App\Models\EstadoAnimal;
use App\Models\Finca;
use App\Models\HistorialAnimal;
use Tests\TestCase;

class AnimalModelTest extends TestCase
{
    public function test_animal_eliminado_no_aparece_en_queries_normales(): void
    {
        $animal = Animal::factory()->create();
        $arete = $animal->numero_arete;
        $animal->delete();

        $this->assertNull(Animal::find($arete));
        $this->assertNotNull(Animal::withTrashed()->find($arete));
    }

    public function test_historiales_ordenados_del_mas_reciente_al_mas_antiguo(): void
    {
        $finca = Finca::factory()->create();
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);
        $asignador = $this->crearPersona('Ganadero');

        HistorialAnimal::factory()->create([
            'numero_arete'     => $animal->numero_arete,
            'cedula_asignador' => $asignador->cedula,
            'created_at'       => now()->subMinute(),
        ]);
        $ultimo = HistorialAnimal::factory()->create([
            'numero_arete'     => $animal->numero_arete,
            'cedula_asignador' => $asignador->cedula,
            'created_at'       => now(),
        ]);

        $this->assertEquals($ultimo->id_historial, $animal->historiales->first()->id_historial);
    }

    public function test_ultimo_historial_retorna_el_registro_mas_reciente(): void
    {
        $finca = Finca::factory()->create();
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);
        $asignador = $this->crearPersona('Ganadero');

        HistorialAnimal::factory()->create([
            'numero_arete'     => $animal->numero_arete,
            'cedula_asignador' => $asignador->cedula,
            'created_at'       => now()->subMinute(),
        ]);
        $ultimo = HistorialAnimal::factory()->create([
            'numero_arete'     => $animal->numero_arete,
            'cedula_asignador' => $asignador->cedula,
            'created_at'       => now(),
        ]);

        $this->assertEquals($ultimo->id_historial, $animal->ultimoHistorial->id_historial);
    }

    public function test_relacion_estado_pertenece_a_estado_animal(): void
    {
        $animal = Animal::factory()->create();
        $this->assertInstanceOf(EstadoAnimal::class, $animal->estado);
    }

    public function test_relacion_finca_pertenece_a_finca(): void
    {
        $animal = Animal::factory()->create();
        $this->assertInstanceOf(Finca::class, $animal->finca);
    }
}
