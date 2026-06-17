<?php

namespace Tests;

use App\Models\Persona;
use App\Models\Rol;
use Database\Seeders\EstadoAnimalSeeder;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolSeeder::class);
        $this->seed(EstadoAnimalSeeder::class);
    }

    protected function crearPersona(string $rol, array $extra = []): Persona
    {
        $idRol = Rol::where('nombre', $rol)->value('id_rol');
        return Persona::factory()->create(array_merge(['id_rol' => $idRol], $extra));
    }

    protected function actingAsPersona(string $rol, array $extra = []): Persona
    {
        $persona = $this->crearPersona($rol, $extra);
        Sanctum::actingAs($persona);
        return $persona;
    }
}
