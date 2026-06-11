<?php

namespace Database\Seeders;

use App\Models\Fundacion;
use Illuminate\Database\Seeder;

class FundacionesSeeder extends Seeder
{
    public function run(): void
    {
        // Crear 20 fundaciones normales
        Fundacion::factory()
            ->count(20)
            ->create();

        // Crear 10 fundaciones verificadas
        Fundacion::factory()
            ->count(10)
            ->verificada()
            ->create();

        // Crear 5 fundaciones que reciben voluntarios
        Fundacion::factory()
            ->count(5)
            ->conVoluntarios()
            ->create();

        // Caso específico
        Fundacion::create([
            'Nombre_1' => 'Fundación Patitas Felices',
            'Direccion' => 'Carrera 45 #67-89',
            'Telefono' => '3119876543',
            'Email' => 'contacto@patitasfelices.org',
            'registro_sanitario' => 'REG-001-ABC',
            'capacidad_maxima' => 150,
            'recibe_voluntarios' => true,
            'verificado' => true,
            'ciudad' => 'Medellín',
            'imagen_portada' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1780343918/avatars/l0d1sk6uty6svqbztdya.jpg',
        ]);
    }
}
