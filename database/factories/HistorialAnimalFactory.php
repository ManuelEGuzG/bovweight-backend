<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\HistorialAnimal;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistorialAnimalFactory extends Factory
{
    protected $model = HistorialAnimal::class;

    public function definition(): array
    {
        $peso = $this->faker->randomFloat(1, 100, 600);

        return [
            'numero_arete'     => Animal::factory(),
            'peso'             => $peso,
            'peso_real'        => $peso,
            'cedula_asignador' => Persona::factory(),
            'metodo'           => 'manual',
            'fecha_de_foto'    => now(),
        ];
    }
}
