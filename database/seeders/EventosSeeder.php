<?php

namespace Database\Seeders;

use App\Models\Evento;
use App\Models\User;
use App\Models\Fundacion;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class EventosSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. OBTENER USUARIOS POR TIPO
        // ==========================================

        $usuarioAdmin = User::where('tipo', 'admin')->first();
        $fundaciones = Fundacion::all();

        // ==========================================
        // 2. EVENTOS ORGANIZADOS POR ADMIN
        // ==========================================

        if ($usuarioAdmin) {
            // Evento 1: Feria de Adopción
            Evento::create([
                'nombre_evento' => 'Gran Feria de Adopción Nacional 2024',
                'lugar_evento' => 'Parque de los Deseos, Bogotá',
                'descripcion' => 'Evento masivo de adopción con más de 200 mascotas buscando un hogar. ¡Ven y encuentra a tu nuevo mejor amigo!',
                'fecha_evento' => Carbon::now()->addDays(45),
                'fecha_fin' => Carbon::now()->addDays(46),
                'imagen_url' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1779295680/eventos/pqqnd795kd69jpnhpnfl.jpg',
                'imagen_public_id' => 'eventos/feria_adopcion_2024',
                'fundacion_id' => null,
                'tipo' => 'admin',
                'capacidad_maxima' => 500,
                'costo' => 'Gratis',
                'organizador' => $usuarioAdmin->nombre . ' ' . $usuarioAdmin->apellidos,
                'telefono_contacto' => $usuarioAdmin->telefono,
                'email_contacto' => $usuarioAdmin->email,
                'categoria' => 'Adopción',
                'tags' => json_encode(['adopcion', 'nacional', 'mascotas']),
                'likes' => 0,
            ]);

            // Evento 2: Campaña de Esterilización
            Evento::create([
                'nombre_evento' => 'Campaña Nacional de Esterilización',
                'lugar_evento' => 'Centro de Convenciones, Medellín',
                'descripcion' => 'Jornada gratuita de esterilización para perros y gatos. ¡Ayuda a controlar la sobrepoblación animal!',
                'fecha_evento' => Carbon::now()->addDays(30),
                'fecha_fin' => Carbon::now()->addDays(31),
                'imagen_url' => 'https://res.cloudinary.com/dixyebg5i/image/upload/v1782460000/pexels-photo-37533934_qs5lxv.avif',
                'imagen_public_id' => 'eventos/campana_esterilizacion',
                'fundacion_id' => null,
                'tipo' => 'admin',
                'capacidad_maxima' => 300,
                'costo' => 'Gratis',
                'organizador' => $usuarioAdmin->nombre . ' ' . $usuarioAdmin->apellidos,
                'telefono_contacto' => $usuarioAdmin->telefono,
                'email_contacto' => $usuarioAdmin->email,
                'categoria' => 'Esterilización',
                'tags' => json_encode(['esterilizacion', 'salud', 'gratis']),
                'likes' => 0,
            ]);
        }

        // ==========================================
        // 3. EVENTOS ORGANIZADOS POR FUNDACIONES
        // ==========================================

        foreach ($fundaciones as $fundacion) {
            $numEventos = rand(1, 3);

            for ($i = 0; $i < $numEventos; $i++) {
                $fechaEvento = Carbon::now()->addDays(rand(5, 90));
                $fechaFin = (clone $fechaEvento)->addDays(rand(1, 5));

                Evento::create([
                    'nombre_evento' => $this->getNombreEvento(),
                    'lugar_evento' => $this->getLugarAleatorio(),
                    'descripcion' => $this->getDescripcionEvento(),
                    'fecha_evento' => $fechaEvento,
                    'fecha_fin' => $fechaFin,
                    'imagen_url' => $this->getImagenAleatoria(),
                    'imagen_public_id' => 'eventos/fundacion_' . $fundacion->id . '_' . uniqid(),
                    'fundacion_id' => $fundacion->id,
                    'tipo' => 'fundacion',
                    'capacidad_maxima' => rand(50, 300),
                    'costo' => $this->getCostoAleatorio(),
                    'organizador' => $fundacion->Nombre_1,
                    'telefono_contacto' => $fundacion->Telefono,
                    'email_contacto' => $fundacion->Email,
                    'categoria' => $this->getCategoriaAleatoria(),
                    'tags' => json_encode([$fundacion->ciudad, 'fundacion', 'evento']),
                    'likes' => rand(0, 200),
                ]);
            }
        }

        // ==========================================
        // 4. EVENTOS ADICIONALES CON FACTORY
        // ==========================================

        Evento::factory()->count(15)->proximo()->create();
        Evento::factory()->count(10)->pasado()->create();
        Evento::factory()->count(5)->create();

        // ==========================================
        // 5. TOTAL DE EVENTOS CREADOS
        // ==========================================

        $total = Evento::count();
        $this->command->info("✅ Total de eventos creados: {$total}");
    }

    // ============ MÉTODOS AUXILIARES ============

    private function getNombreEvento(): string
    {
        $nombres = [
            'Jornada de Adopción Comunitaria',
            'Campaña de Vacunación Gratuita',
            'Feria de Bienestar Animal',
            'Encuentro de Mascotas',
            'Concierto Solidario por los Animales',
            'Taller de Tenencia Responsable',
            'Brigada Veterinaria Móvil',
            'Recaudación de Fondos',
            'Desfile de Mascotas',
            'Jornada de Esterilización'
        ];
        return $nombres[array_rand($nombres)];
    }

    private function getLugarAleatorio(): string
    {
        $lugares = [
            'Parque Caldas, Popayán',
            'Centro Comercial Campanario, Popayán',
            'Universidad del Cauca',
            'Polideportivo Tulcán, Popayán',
            'Parque de los Deseos, Bogotá',
            'Plaza de Bolívar, Bogotá',
            'Parque Simón Bolívar, Bogotá',
            'Centro de Convenciones, Medellín',
            'Coliseo Cubierto, Cali'
        ];
        return $lugares[array_rand($lugares)];
    }

    private function getDescripcionEvento(): string
    {
        $descripciones = [
            'Evento destinado al bienestar y protección de los animales. ¡Te esperamos!',
            'Jornada comunitaria para promover la adopción responsable y el cuidado animal.',
            'Actividad organizada para recaudar fondos destinados a refugios y fundaciones.',
            'Espacio educativo para fortalecer la tenencia responsable de mascotas.',
            'Evento abierto a toda la comunidad para apoyar el bienestar de perros y gatos.',
            'Únete a esta causa noble y ayuda a los animales que más lo necesitan.'
        ];
        return $descripciones[array_rand($descripciones)];
    }

    private function getImagenAleatoria(): string
    {
        $imagenes = [
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782460000/pexels-photo-37533934_qs5lxv.avif',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782460000/pexels-photo-33313537_izxpkh.avif',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459999/pexels-photo-28483933_mk0rv5.avif',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459999/pexels-photo-33313535_ed012c.avif',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782459998/pexels-photo-16620581_ob9b9x.avif',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1779295680/eventos/pqqnd795kd69jpnhpnfl.jpg',
            'https://res.cloudinary.com/dixyebg5i/image/upload/v1782414979/eventos/kkhe3fxy6u5hqbdf5q70.jpg',
        ];
        return $imagenes[array_rand($imagenes)];
    }

    private function getCostoAleatorio(): string
    {
        $costos = ['Gratis', '$10.000', '$20.000', '$30.000', '$50.000'];
        return $costos[array_rand($costos)];
    }

    private function getCategoriaAleatoria(): string
    {
        $categorias = ['Adopción', 'Vacunación', 'Esterilización', 'Bienestar Animal', 'Recaudación', 'Concierto Solidario'];
        return $categorias[array_rand($categorias)];
    }
}
