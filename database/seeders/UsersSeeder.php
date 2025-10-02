<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\TarifasEnum;
use App\Enums\AmbientesEnum;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('123456789'),
            // 'tarifa' => TarifasEnum::COMPROBANTE->value,
            // 'ambiente' => AmbientesEnum::PRUEBAS->value,
            // 'ruc' => '0202433918001',
            // 'razonSocial' => 'LUNA ARTEAGA ALEXANDER PAUL',
            // 'nombreComercial' => 'Ave Joyas',
            // 'dirMatriz' => 'Guaranda',
            // 'obligadoContabilidad' => true,
        ]);
        $user->assignRole('admin');
    }
}
