<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\Finca;
use App\Models\HistorialAnimal;
use App\Services\MlService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HistorialTest extends TestCase
{
    private function prepararEscenario(): array
    {
        $persona = $this->actingAsPersona('Ganadero');
        $finca = Finca::factory()->create();
        $finca->personas()->attach($persona->cedula, ['es_dueno' => true]);
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);
        return [$persona, $finca, $animal];
    }

    // Verifica que el historial de pesajes se retorna paginado con los registros correctos
    public function test_listar_historial_paginado(): void
    {
        [$persona, $finca, $animal] = $this->prepararEscenario();
        HistorialAnimal::factory()->count(5)->create([
            'numero_arete'     => $animal->numero_arete,
            'cedula_asignador' => $persona->cedula,
        ]);

        $response = $this->getJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/historial"
        )->assertOk();

        $this->assertArrayHasKey('data', $response->json());
        $this->assertCount(5, $response->json('data'));
    }

    // Verifica que se puede registrar un pesaje manual enviando solo el campo 'peso'
    public function test_crear_pesaje_manual(): void
    {
        [$persona, $finca, $animal] = $this->prepararEscenario();

        $this->postJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/historial",
            ['peso' => 350.5]
        )->assertStatus(201)
         ->assertJsonFragment(['metodo' => 'manual']);
    }

    // Verifica que un peso por debajo del mínimo permitido falla con error de validación 422
    public function test_crear_pesaje_manual_peso_por_debajo_del_minimo(): void
    {
        [$persona, $finca, $animal] = $this->prepararEscenario();

        $this->postJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/historial",
            ['peso' => 10]
        )->assertStatus(422)
         ->assertJsonValidationErrors(['peso']);
    }

    // Verifica que un peso por encima del máximo permitido falla con error de validación 422
    public function test_crear_pesaje_manual_peso_por_encima_del_maximo(): void
    {
        [$persona, $finca, $animal] = $this->prepararEscenario();

        $this->postJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/historial",
            ['peso' => 2000]
        )->assertStatus(422)
         ->assertJsonValidationErrors(['peso']);
    }

    // Verifica que crear un pesaje sin foto ni peso falla indicando que ambos campos son requeridos
    public function test_crear_pesaje_sin_foto_ni_peso_falla(): void
    {
        [$persona, $finca, $animal] = $this->prepararEscenario();

        $this->postJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/historial",
            []
        )->assertStatus(422)
         ->assertJsonValidationErrors(['foto', 'peso']);
    }

    // Verifica que se puede registrar un pesaje vía fotografía cuando el servicio ML responde correctamente
    public function test_crear_pesaje_por_foto_con_ml_exitoso(): void
    {
        Storage::fake('public');

        $this->mock(MlService::class, function ($mock) {
            $mock->shouldReceive('estimateWeight')
                ->once()
                ->andReturn([
                    'bovine_detected'     => true,
                    'estimated_weight_kg' => 380.0,
                    'confidence'          => 0.91,
                    'bounding_box'        => [5, 10, 320, 400],
                ]);
        });

        [$persona, $finca, $animal] = $this->prepararEscenario();
        $foto = UploadedFile::fake()->image('bovino.jpg', 640, 480);

        $this->post(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/historial",
            ['foto' => $foto],
            ['Accept' => 'application/json']
        )->assertStatus(201)
         ->assertJsonFragment(['metodo' => 'fotografia']);
    }

    // Verifica que se puede consultar el detalle de un registro de historial específico
    public function test_ver_pesaje_especifico(): void
    {
        [$persona, $finca, $animal] = $this->prepararEscenario();
        $historial = HistorialAnimal::factory()->create([
            'numero_arete'     => $animal->numero_arete,
            'cedula_asignador' => $persona->cedula,
        ]);

        $this->getJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/historial/{$historial->id_historial}"
        )->assertOk()
         ->assertJsonFragment(['id_historial' => $historial->id_historial]);
    }

    // Verifica que se puede registrar el peso real de un pesaje y el servicio ML recibe el feedback
    public function test_corregir_peso_real(): void
    {
        $this->mock(MlService::class, function ($mock) {
            $mock->shouldReceive('sendFeedback')->once()->andReturnNull();
        });

        [$persona, $finca, $animal] = $this->prepararEscenario();
        $historial = HistorialAnimal::factory()->create([
            'numero_arete'     => $animal->numero_arete,
            'cedula_asignador' => $persona->cedula,
            'peso'             => 300.0,
        ]);

        $this->patchJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/historial/{$historial->id_historial}/corregir",
            ['peso_real' => 320.0]
        )->assertOk();

        $this->assertEquals(320.0, $historial->fresh()->peso_real);
    }

    // Verifica que un peso real fuera del rango permitido falla con error de validación 422
    public function test_corregir_peso_real_invalido(): void
    {
        [$persona, $finca, $animal] = $this->prepararEscenario();
        $historial = HistorialAnimal::factory()->create([
            'numero_arete'     => $animal->numero_arete,
            'cedula_asignador' => $persona->cedula,
        ]);

        $this->patchJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/historial/{$historial->id_historial}/corregir",
            ['peso_real' => 5000]
        )->assertStatus(422)
         ->assertJsonValidationErrors(['peso_real']);
    }

    // Verifica que consultar el historial de una finca no asignada retorna 403
    public function test_historial_finca_no_asignada_retorna_403(): void
    {
        $this->actingAsPersona('Ganadero');
        $finca = Finca::factory()->create();
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);

        $this->getJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/historial"
        )->assertForbidden();
    }
}
