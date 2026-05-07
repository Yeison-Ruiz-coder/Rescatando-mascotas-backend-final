<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RazaSeeder::class,
            TipoVacunaSeeder::class,
            FundacionSeeder::class
        ]);
    }
}
