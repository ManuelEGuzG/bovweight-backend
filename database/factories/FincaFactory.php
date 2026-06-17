<?php

namespace Database\Factories;

use App\Models\Finca;
use Illuminate\Database\Eloquent\Factories\Factory;

class FincaFactory extends Factory
{
    protected $model = Finca::class;

    public function definition(): array
    {
        return [
            'nombre'    => $this->faker->company(),
            'ubicacion' => $this->faker->city() . ', Costa Rica',
            'hectareas' => $this->faker->randomFloat(2, 5, 500),
            'notas'     => $this->faker->optional()->sentence(),
            'activo'    => true,
        ];
    }
}
