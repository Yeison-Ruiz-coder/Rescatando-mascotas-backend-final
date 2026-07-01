<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. USUARIOS (Admin, User, Fundación, Veterinaria)
            UserSeeder::class,

            // 2. RAZAS
            RazaSeeder::class,

            // 3. TIPOS DE VACUNA
            TipoVacunaSeeder::class,

            // 4. FUNDACIONES (crea las fundaciones vinculadas a usuarios y adicionales)
            FundacionesSeeder::class,

            // 5. VETERINARIAS
            VeterinariasSeeder::class,

            // 6. EVENTOS (repartidos entre admin, fundacion, veterinaria)
            EventosSeeder::class,

            // 7. MASCOTAS (repartidas entre todas las fundaciones)
            MascotasSeeder::class,

            // 8. RESCATES (diferentes ubicaciones en Popayán)
            RescatesSeeder::class,

            // 9. SUSCRIPCIONES A MASCOTAS DE PATITAS FELICES
            SuscripcionesSeeder::class,

            // 10. SOLICITUDES DE ADOPCIÓN
            SolicitudesSeeder::class,
        ]);
    }
}
