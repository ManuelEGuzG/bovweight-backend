<?php

namespace Tests\Integration;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Medicamento;
use App\Models\Persona;
use App\Models\Receta;
use Tests\TestCase;

class RecetaModelTest extends TestCase
{
    private function crearReceta(): Receta
    {
        $finca = Finca::factory()->create();
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);
        $vet = $this->crearPersona('Veterinario');
        $medicamento = Medicamento::factory()->create();

        return Receta::factory()->create([
            'numero_arete'       => $animal->numero_arete,
            'cedula_veterinario' => $vet->cedula,
            'id_medicamento'     => $medicamento->id_medicamento,
        ]);
    }

    public function test_relacion_medicamento_pertenece_a_medicamento(): void
    {
        $receta = $this->crearReceta();
        $this->assertInstanceOf(Medicamento::class, $receta->medicamento);
    }

    public function test_relacion_veterinario_pertenece_a_persona(): void
    {
        $receta = $this->crearReceta();
        $this->assertInstanceOf(Persona::class, $receta->veterinario);
    }

    public function test_relacion_animal_pertenece_a_animal(): void
    {
        $receta = $this->crearReceta();
        $this->assertInstanceOf(Animal::class, $receta->animal);
    }
}
