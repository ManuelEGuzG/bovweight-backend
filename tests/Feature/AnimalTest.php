<?php

namespace Tests\Feature;

use App\Models\Animal;
use App\Models\EstadoAnimal;
use App\Models\Finca;
use Tests\TestCase;

class AnimalTest extends TestCase
{
    private function crearFincaConAcceso(string $rol, bool $esDueno = true): array
    {
        $persona = $this->actingAsPersona($rol);
        $finca = Finca::factory()->create();
        $finca->personas()->attach($persona->cedula, ['es_dueno' => $esDueno]);
        return [$persona, $finca];
    }

    public function test_listar_animales_de_finca_accesible(): void
    {
        [$persona, $finca] = $this->crearFincaConAcceso('Ganadero');
        Animal::factory()->count(3)->create(['id_finca' => $finca->id_finca]);

        $this->getJson("/api/v1/fincas/{$finca->id_finca}/animales")
            ->assertOk()
            ->assertJsonCount(3);
    }

    public function test_no_puede_listar_animales_de_finca_no_asignada(): void
    {
        $this->actingAsPersona('Ganadero');
        $finca = Finca::factory()->create();

        $this->getJson("/api/v1/fincas/{$finca->id_finca}/animales")
            ->assertForbidden();
    }

    public function test_crear_animal_con_datos_validos(): void
    {
        [$persona, $finca] = $this->crearFincaConAcceso('Ganadero');

        $this->postJson("/api/v1/fincas/{$finca->id_finca}/animales", [
            'numero_arete' => 'AR-001',
            'sexo'         => 'macho',
            'raza'         => 'Angus',
        ])->assertStatus(201)
          ->assertJsonFragment(['numero_arete' => 'AR-001']);
    }

    public function test_crear_animal_sin_estado_asigna_bien_por_defecto(): void
    {
        [$persona, $finca] = $this->crearFincaConAcceso('Ganadero');

        $this->postJson("/api/v1/fincas/{$finca->id_finca}/animales", [
            'numero_arete' => 'AR-002',
            'sexo'         => 'hembra',
            'raza'         => 'Holstein',
        ])->assertStatus(201);

        $animal = Animal::find('AR-002');
        $this->assertEquals('Bien', $animal->estado->nombre_estado);
    }

    public function test_crear_animal_numero_arete_duplicado(): void
    {
        [$persona, $finca] = $this->crearFincaConAcceso('Ganadero');
        Animal::factory()->create(['numero_arete' => 'AR-DUP', 'id_finca' => $finca->id_finca]);

        $this->postJson("/api/v1/fincas/{$finca->id_finca}/animales", [
            'numero_arete' => 'AR-DUP',
            'sexo'         => 'macho',
            'raza'         => 'Angus',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['numero_arete']);
    }

    public function test_crear_animal_sexo_invalido(): void
    {
        [$persona, $finca] = $this->crearFincaConAcceso('Ganadero');

        $this->postJson("/api/v1/fincas/{$finca->id_finca}/animales", [
            'numero_arete' => 'AR-003',
            'sexo'         => 'neutro',
            'raza'         => 'Angus',
        ])->assertStatus(422)
          ->assertJsonValidationErrors(['sexo']);
    }

    public function test_ver_animal_especifico(): void
    {
        [$persona, $finca] = $this->crearFincaConAcceso('Ganadero');
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);

        $this->getJson("/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}")
            ->assertOk()
            ->assertJsonFragment(['numero_arete' => $animal->numero_arete]);
    }

    public function test_actualizar_animal(): void
    {
        [$persona, $finca] = $this->crearFincaConAcceso('Ganadero');
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);

        $this->putJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}",
            ['nombre' => 'Pepita']
        )->assertOk()
         ->assertJsonFragment(['nombre' => 'Pepita']);
    }

    public function test_eliminar_animal_soft_delete(): void
    {
        [$persona, $finca] = $this->crearFincaConAcceso('Ganadero');
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);

        $this->deleteJson("/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}")
            ->assertOk();

        $this->assertSoftDeleted($animal);
    }

    public function test_cambiar_estado_animal(): void
    {
        [$persona, $finca] = $this->crearFincaConAcceso('Ganadero');
        $animal = Animal::factory()->create(['id_finca' => $finca->id_finca]);
        $estado = EstadoAnimal::where('nombre_estado', 'Enfermo')->first();

        $this->patchJson(
            "/api/v1/fincas/{$finca->id_finca}/animales/{$animal->numero_arete}/estado",
            ['id_estado' => $estado->id_estado]
        )->assertOk();

        $this->assertEquals('Enfermo', $animal->fresh()->estado->nombre_estado);
    }

    public function test_admin_puede_crear_animal_en_cualquier_finca(): void
    {
        $this->actingAsPersona('Administrador');
        $finca = Finca::factory()->create();

        $this->postJson("/api/v1/fincas/{$finca->id_finca}/animales", [
            'numero_arete' => 'AR-ADMIN',
            'sexo'         => 'macho',
            'raza'         => 'Brahman',
        ])->assertStatus(201);
    }
}
