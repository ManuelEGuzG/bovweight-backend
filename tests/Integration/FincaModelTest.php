<?php

namespace Tests\Integration;

use App\Models\Animal;
use App\Models\Finca;
use Tests\TestCase;

class FincaModelTest extends TestCase
{
    // Verifica que una finca eliminada no aparece en consultas normales pero sí con withTrashed()
    public function test_finca_eliminada_no_aparece_en_queries_normales(): void
    {
        $finca = Finca::factory()->create();
        $id = $finca->id_finca;
        $finca->delete();

        $this->assertNull(Finca::find($id));
        $this->assertNotNull(Finca::withTrashed()->find($id));
    }

    // Verifica que la relación 'animales' devuelve instancias correctas del modelo Animal
    public function test_relacion_animales_retorna_instancias_de_animal(): void
    {
        $finca = Finca::factory()->create();
        Animal::factory()->count(3)->create(['id_finca' => $finca->id_finca]);

        $this->assertCount(3, $finca->animales);
        $this->assertInstanceOf(Animal::class, $finca->animales->first());
    }

    // Verifica que la relación 'animales' retorna colección vacía cuando la finca no tiene animales
    public function test_finca_sin_animales_retorna_coleccion_vacia(): void
    {
        $finca = Finca::factory()->create();
        $this->assertCount(0, $finca->animales);
    }
}
