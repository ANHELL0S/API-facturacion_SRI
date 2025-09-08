<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // $this->call(ConfigurationsSeeder::class);

        $this->call(ErroresSriSeeder::class);
        // $this->call(ImpuestosSeeder::class);
        // $this->call(TarifasImpuestosSeeder::class);

        $this->call(PermissionsSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(EstablecimientosSeeder::class);
        $this->call(PuntosEmisionSeeder::class);
    }
}
