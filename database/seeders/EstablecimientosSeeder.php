<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Establecimiento;
use App\Models\User;
use App\Enums\RolesEnum;

class EstablecimientosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Obtener un usuario existente o crear uno nuevo
        $user = User::role(RolesEnum::CLIENTE)->find(2);

        Establecimiento::create([
            'user_id' => $user->id,
            'nombre' => 'Establecimiento 1',
            'numero' => '001',
            'direccion' => 'Calle Olmedo',
        ]);
    }
}
