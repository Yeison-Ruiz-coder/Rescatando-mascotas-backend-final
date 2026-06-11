<?php

namespace Database\Seeders;

use App\Models\Evento;
use Illuminate\Database\Seeder;

class EventosSeeder extends Seeder
{
    public function run(): void
    {
        // Crear 15 eventos próximos
        Evento::factory()
            ->count(15)
            ->proximo()
            ->create();

        // Crear 10 eventos pasados
        Evento::factory()
            ->count(10)
            ->pasado()
            ->create();

        // Crear 5 eventos normales (fechas aleatorias)
        Evento::factory()
            ->count(5)
            ->create();

        // Evento destacado específico
        Evento::create([
            'nombre_evento' => 'Gran Feria de Adopción 2024',
            'lugar_evento' => 'Parque de los Deseos',
            'descripcion' => 'Ven a conocer a nuestros peluditos que buscan un hogar',
            'fecha_evento' => now()->addDays(30),
            'fecha_fin' => now()->addDays(31),
            'categoria' => 'Adopción',
            'costo' => 'Gratis',
            'capacidad_maxima' => 500,
            'imagen_url' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1779295680/eventos/pqqnd795kd69jpnhpnfl.jpg',
            'likes' => 0,
        ]);
    }
}
