<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PuntoEmision;
use App\Models\Establecimiento;

class PuntosEmisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $establecimiento1 = Establecimiento::where('numero', '001')->first();

        PuntoEmision::create([
            'establecimiento_id' => $establecimiento1->id,
            'numero' => '001',
            'nombre' => 'Punto de Emision 1',
        ]);
    }
}
