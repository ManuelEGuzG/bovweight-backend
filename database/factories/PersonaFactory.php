<?php

namespace Database\Factories;

use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class PersonaFactory extends Factory
{
    protected $model = Persona::class;

    public function definition(): array
    {
        return [
            'cedula'     => $this->faker->unique()->numerify('##########'),
            'nombre'     => $this->faker->firstName(),
            'apellidos'  => $this->faker->lastName() . ' ' . $this->faker->lastName(),
            'correo'     => $this->faker->unique()->safeEmail(),
            'contrasena' => Hash::make('password'),
            'contacto'   => $this->faker->phoneNumber(),
            'id_rol'     => fn() => Rol::where('nombre', 'Ganadero')->value('id_rol'),
            'activo'     => true,
        ];
    }
}
