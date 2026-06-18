<?php

namespace Database\Seeders;

use App\Models\Medicamento;
use Illuminate\Database\Seeder;

class MedicamentoExtraSeeder extends Seeder
{
    public function run(): void
    {
        $medicamentos = [
            ['nombre' => 'Albendazol',          'descripcion' => 'Antiparasitario oral de amplio espectro contra parásitos internos.'],
            ['nombre' => 'Dexametasona',         'descripcion' => 'Corticoide antiinflamatorio usado en partos difíciles e inflamaciones.'],
            ['nombre' => 'Flunixin Meglumine',   'descripcion' => 'Antiinflamatorio no esteroideo (AINE) para fiebre y dolor.'],
            ['nombre' => 'Calcio EV',            'descripcion' => 'Suplemento de calcio intravenoso para hipocalcemia post-parto.'],
            ['nombre' => 'Vacuna Aftosa',        'descripcion' => 'Vacuna contra la fiebre aftosa, obligatoria en Costa Rica.'],
            ['nombre' => 'Oxitocina',            'descripcion' => 'Hormona para estimular contracciones uterinas en partos.'],
            ['nombre' => 'Hierro Dextrano',      'descripcion' => 'Suplemento de hierro inyectable para terneros anémicos.'],
            ['nombre' => 'Vitamina B12',         'descripcion' => 'Suplemento vitamínico para mejorar energía y metabolismo.'],
            ['nombre' => 'Clostrisan',           'descripcion' => 'Vacuna polivalente contra clostridiosis bovina.'],
            ['nombre' => 'Sulfato de Magnesio',  'descripcion' => 'Tratamiento para tetania de los pastos por deficiencia de magnesio.'],
            ['nombre' => 'Enrofloxacina',        'descripcion' => 'Antibiótico moderno de amplio espectro para infecciones bacterianas.'],
            ['nombre' => 'Cloruro de Sodio 0.9%','descripcion' => 'Suero fisiológico para hidratación y dilución de medicamentos.'],
        ];

        foreach ($medicamentos as $med) {
            Medicamento::firstOrCreate(['nombre' => $med['nombre']], $med);
        }
    }
}
