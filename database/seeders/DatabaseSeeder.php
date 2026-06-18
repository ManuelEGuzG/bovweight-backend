<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            EstadoAnimalSeeder::class,
            MedicamentoSeeder::class,
            MedicamentoExtraSeeder::class,
            PersonaSeeder::class,
            FincaSeeder::class,
            AnimalSeeder::class,
        ]);
    }
}
