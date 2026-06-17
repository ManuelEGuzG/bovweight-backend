<?php

namespace Database\Factories;

use App\Models\Animal;
use App\Models\Medicamento;
use App\Models\Persona;
use App\Models\Receta;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecetaFactory extends Factory
{
    protected $model = Receta::class;

    public function definition(): array
    {
        return [
            'numero_arete'       => Animal::factory(),
            'id_medicamento'     => Medicamento::factory(),
            'cedula_veterinario' => Persona::factory(),
            'dosis'              => $this->faker->randomElement(['5ml', '10mg', '2 tabletas']),
            'frecuencia'         => $this->faker->randomElement(['Cada 8 horas', 'Una vez al día', 'Cada 12 horas']),
            'duracion_dias'      => $this->faker->numberBetween(3, 30),
            'fecha_emision'      => now()->toDateString(),
            'diagnostico'        => $this->faker->optional()->sentence(),
            'notas'              => $this->faker->optional()->sentence(),
        ];
    }
}
