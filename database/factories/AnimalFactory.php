<?php

namespace Database\Factories;

use App\Models\Animal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Animal>
 */
class AnimalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numero_arete'     => $this->faker->unique()->bothify('AR-####??'),
            'nombre'           => $this->faker->optional()->firstName(),
            'id_finca'         => \App\Models\Finca::factory(),
            'sexo'             => $this->faker->randomElement(['macho', 'hembra']),
            'raza'             => $this->faker->randomElement(['Holstein', 'Angus', 'Brahman', 'Jersey']),
            'fecha_nacimiento' => $this->faker->optional()->date(),
            'nota_general'     => $this->faker->optional()->sentence(),
            'id_estado'        => fn() => \App\Models\EstadoAnimal::where('nombre_estado', 'Bien')->value('id_estado'),
            'activo'           => true,
        ];
    }
}
