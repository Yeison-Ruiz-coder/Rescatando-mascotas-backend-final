<?php

namespace Database\Seeders;

use App\Models\Fundacion;
use Database\Factories\FundacionFactory;
use Illuminate\Database\Seeder;

class FundacionesSeeder extends Seeder
{
    public function run(): void
    {
        FundacionFactory::resetImagenesUsadas();
        $imagenManual = 'https://res.cloudinary.com/dixyebg5i/image/upload/v1782460777/images_nbmj0r.png';
        FundacionFactory::registrarImagenUsada($imagenManual);

        $totalImagenes = count(FundacionFactory::imagenesDisponibles());
        $imagenesRestantes = max(0, $totalImagenes - count(FundacionFactory::imagenesUsadas()));

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
            'imagen_portada' => $imagenManual,
        ]);
    }
}
