<?php

namespace Database\Seeders;

use App\Models\Mascota;
use Illuminate\Database\Seeder;

class MascotasSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ LO MEJOR: Combinar Factory + Seeder

        // 1. Crear 50 mascotas normales
        Mascota::factory()
            ->count(50)
            ->create();

        // 2. Crear 10 mascotas destacadas
        Mascota::factory()
            ->count(10)
            ->destacada()
            ->create();

        // 3. Crear 5 mascotas ya adoptadas
        Mascota::factory()
            ->count(5)
            ->adoptada()
            ->create();

        // 4. Crear 3 mascotas con video
        Mascota::factory()
            ->count(3)
            ->conVideo()
            ->create();

        // 5. Casos específicos (solo con Seeder)
        Mascota::create([
            'nombre_mascota' => 'Firulais Especial',
            'especie' => 'Perro',
            'tamano' => 'grande',
            'foto_principal' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1780966318/mascotas/izh6m04j0ratewpjrd79.jpg',
            'foto_principal_public_id' => 'sample',
            'descripcion' => 'Caso especial que necesita atención médica',
            'condiciones_especiales' => 'Requiere medicación diaria',
            'salud_general' => 'En tratamiento por enfermedad crónica',
        ]);
    }
}
