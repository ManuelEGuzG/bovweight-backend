<?php

namespace Tests\Integration;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\HistorialAnimal;
use App\Models\Persona;
use Tests\TestCase;

class HistorialAnimalModelTest extends TestCase
{
    private function crearHistorial(): HistorialAnimal
    {
        $finca = Finca::factory()->create();
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);
        $asignador = $this->crearPersona('Ganadero');

        return HistorialAnimal::factory()->create([
            'numero_arete'     => $animal->numero_arete,
            'cedula_asignador' => $asignador->cedula,
        ]);
    }

    public function test_relacion_asignador_pertenece_a_persona(): void
    {
        $historial = $this->crearHistorial();
        $this->assertInstanceOf(Persona::class, $historial->asignador);
    }

    public function test_relacion_animal_pertenece_a_animal(): void
    {
        $historial = $this->crearHistorial();
        $this->assertInstanceOf(Animal::class, $historial->animal);
    }
}
