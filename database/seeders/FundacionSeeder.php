<?php

namespace Database\Seeders;

use App\Models\Fundacion;
use App\Models\User;
use Illuminate\Database\Seeder;

class FundacionesSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. FUNDACIONES VINCULADAS A USUARIOS
        // ==========================================

        // Buscar el usuario de tipo 'fundacion' (ID 3 en UserSeeder)
        $usuarioFundacion = User::where('tipo', 'fundacion')->first();

        if ($usuarioFundacion) {
            // Verificar si ya existe una fundación para este usuario
            $fundacionExistente = Fundacion::where('user_id', $usuarioFundacion->id)->first();

            if (!$fundacionExistente) {
                Fundacion::create([
                    'user_id' => $usuarioFundacion->id,
                    'Nombre_1' => $usuarioFundacion->apellidos, // "Patitas Felices"
                    'Direccion' => $usuarioFundacion->direccion,
                    'Telefono' => $usuarioFundacion->telefono,
                    'Email' => $usuarioFundacion->email,
                    'registro_sanitario' => 'REG-001-ABC',
                    'capacidad_maxima' => 150,
                    'recibe_voluntarios' => true,
                    'verificado' => true,
                    'ciudad' => $usuarioFundacion->ciudad,
                    'lat' => $usuarioFundacion->lat,
                    'lng' => $usuarioFundacion->lng,
                    'radio_atencion' => 5000,
                    'imagen_portada' => $usuarioFundacion->avatar,
                    'imagen_portada_public_id' => $usuarioFundacion->avatar_public_id,
                    'necesidades_actuales' => json_encode(['alimento', 'medicinas', 'voluntarios']),
                    'horario_atencion' => 'Lunes a Viernes 9:00 AM - 6:00 PM',
                    'fecha_fundacion' => '2015-03-15',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // ==========================================
        // 2. FUNDACIONES ADICIONALES CON FACTORY
        // ==========================================

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
    }
}
