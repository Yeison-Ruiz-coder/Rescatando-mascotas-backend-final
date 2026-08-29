<?php

namespace Database\Seeders;

use App\Models\Fundacion;
use App\Models\User;
use Database\Factories\FundacionFactory;
use Illuminate\Database\Seeder;

class FundacionSeeder extends Seeder
{
    public function run(): void
    {
        FundacionFactory::resetImagenesUsadas();
        $imagenManual = 'https://res.cloudinary.com/dixyebg5i/image/upload/v1782460777/images_nbmj0r.png';
        FundacionFactory::registrarImagenUsada($imagenManual);

        $totalImagenes = count(FundacionFactory::imagenesDisponibles());
        $imagenesRestantes = max(0, $totalImagenes - count(FundacionFactory::imagenesUsadas()));

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
                    'Nombre_1' => $usuarioFundacion->apellidos,
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
                    'imagen_portada' => $imagenManual,
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
            ->count(min(20, $imagenesRestantes))
            ->create();

        $imagenesRestantes = max(0, $totalImagenes - count(FundacionFactory::imagenesUsadas()));

        // Crear 10 fundaciones verificadas
        Fundacion::factory()
            ->count(min(10, $imagenesRestantes))
            ->verificada()
            ->create();

        $imagenesRestantes = max(0, $totalImagenes - count(FundacionFactory::imagenesUsadas()));

        // Crear 5 fundaciones que reciben voluntarios
        Fundacion::factory()
            ->count(min(5, $imagenesRestantes))
            ->conVoluntarios()
            ->create();
    }
}
