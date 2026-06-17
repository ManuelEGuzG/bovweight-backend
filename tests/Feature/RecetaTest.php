<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\Medicamento;
use App\Models\Receta;
use Tests\TestCase;

class RecetaTest extends TestCase
{
    private function prepararEscenarioVet(): array
    {
        $vet = $this->actingAsPersona('Veterinario');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($vet->cedula, ['es_dueno' => false]);
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);
        $medicamento = Medicamento::factory()->create();
        return [$vet, $finca, $animal, $medicamento];
    }

    public function test_listar_recetas_de_animal(): void
    {
        [$vet, $finca, $animal, $medicamento] = $this->prepararEscenarioVet();
        Receta::factory()->count(2)->create([
            'numero_arete'       => $animal->numero_arete,
            'id_medicamento'     => $medicamento->id_medicamento,
            'cedula_veterinario' => $vet->cedula,
        ]);

        $this->getJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/recetas"
        )->assertOk()
         ->assertJsonCount(2);
    }

    public function test_veterinario_con_acceso_puede_crear_receta(): void
    {
        [$vet, $finca, $animal, $medicamento] = $this->prepararEscenarioVet();

        $this->postJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/recetas",
            [
                'id_medicamento' => $medicamento->id_medicamento,
                'dosis'          => '5ml cada 8 horas',
                'frecuencia'     => 'Cada 8 horas',
                'duracion_dias'  => 7,
                'diagnostico'    => 'Infección leve',
            ]
        )->assertStatus(201);
    }

    public function test_ganadero_no_puede_crear_receta(): void
    {
        $ganadero = $this->actingAsPersona('Ganadero');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($ganadero->cedula, ['es_dueno' => true]);
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);
        $medicamento = Medicamento::factory()->create();

        $this->postJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/recetas",
            [
                'id_medicamento' => $medicamento->id_medicamento,
                'dosis'          => '5ml',
                'frecuencia'     => 'Cada 8 horas',
                'duracion_dias'  => 7,
            ]
        )->assertForbidden();
    }

    public function test_asistente_no_puede_crear_receta(): void
    {
        $asistente = $this->actingAsPersona('Asistente');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($asistente->cedula, ['es_dueno' => false]);
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);
        $medicamento = Medicamento::factory()->create();

        $this->postJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/recetas",
            [
                'id_medicamento' => $medicamento->id_medicamento,
                'dosis'          => '5ml',
                'frecuencia'     => 'Cada 8 horas',
                'duracion_dias'  => 7,
            ]
        )->assertForbidden();
    }

    public function test_crear_receta_medicamento_inexistente(): void
    {
        [$vet, $finca, $animal, $medicamento] = $this->prepararEscenarioVet();

        $this->postJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/recetas",
            [
                'id_medicamento' => 99999,
                'dosis'          => '5ml',
                'frecuencia'     => 'Cada 8 horas',
                'duracion_dias'  => 7,
            ]
        )->assertStatus(422)
         ->assertJsonValidationErrors(['id_medicamento']);
    }

    public function test_crear_receta_duracion_dias_invalida(): void
    {
        [$vet, $finca, $animal, $medicamento] = $this->prepararEscenarioVet();

        $this->postJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/recetas",
            [
                'id_medicamento' => $medicamento->id_medicamento,
                'dosis'          => '5ml',
                'frecuencia'     => 'Cada 8 horas',
                'duracion_dias'  => 400,
            ]
        )->assertStatus(422)
         ->assertJsonValidationErrors(['duracion_dias']);
    }

    public function test_ver_receta_especifica(): void
    {
        [$vet, $finca, $animal, $medicamento] = $this->prepararEscenarioVet();
        $receta = Receta::factory()->create([
            'numero_arete'       => $animal->numero_arete,
            'id_medicamento'     => $medicamento->id_medicamento,
            'cedula_veterinario' => $vet->cedula,
        ]);

        $this->getJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/recetas/{$receta->id_receta}"
        )->assertOk()
         ->assertJsonFragment(['id_receta' => $receta->id_receta]);
    }

    public function test_veterinario_puede_eliminar_su_propia_receta(): void
    {
        [$vet, $finca, $animal, $medicamento] = $this->prepararEscenarioVet();
        $receta = Receta::factory()->create([
            'numero_arete'       => $animal->numero_arete,
            'id_medicamento'     => $medicamento->id_medicamento,
            'cedula_veterinario' => $vet->cedula,
        ]);

        $this->deleteJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/recetas/{$receta->id_receta}"
        )->assertOk();

        $this->assertDatabaseMissing('recetas', ['id_receta' => $receta->id_receta]);
    }

    public function test_veterinario_no_puede_eliminar_receta_de_otro_veterinario(): void
    {
        $vet1 = $this->actingAsPersona('Veterinario');
        $vet2 = $this->crearPersona('Veterinario');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($vet1->cedula, ['es_dueno' => false]);
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);
        $medicamento = Medicamento::factory()->create();

        $receta = Receta::factory()->create([
            'numero_arete'       => $animal->numero_arete,
            'id_medicamento'     => $medicamento->id_medicamento,
            'cedula_veterinario' => $vet2->cedula,
        ]);

        $this->deleteJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/recetas/{$receta->id_receta}"
        )->assertForbidden();
    }

    public function test_admin_puede_eliminar_cualquier_receta(): void
    {
        $this->actingAsPersona('Administrador');
        $finca = Finca::factory()->create();
        $vet = $this->crearPersona('Veterinario');
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);
        $medicamento = Medicamento::factory()->create();

        $receta = Receta::factory()->create([
            'numero_arete'       => $animal->numero_arete,
            'id_medicamento'     => $medicamento->id_medicamento,
            'cedula_veterinario' => $vet->cedula,
        ]);

        $this->deleteJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/recetas/{$receta->id_receta}"
        )->assertOk();

        $this->assertDatabaseMissing('recetas', ['id_receta' => $receta->id_receta]);
    }
}
