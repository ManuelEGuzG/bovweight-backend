<?php

namespace Database\Factories;

use App\Models\Medicamento;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicamentoFactory extends Factory
{
    protected $model = Medicamento::class;

    public function definition(): array
    {
        return [
            'nombre'      => 'Med_' . $this->faker->unique()->numberBetween(1, 99999),
            'descripcion' => $this->faker->sentence(),
        ];
    }
}
