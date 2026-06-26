<?php

namespace Database\Seeders;

use App\Models\Mascota;
use App\Models\Fundacion;
use Illuminate\Database\Seeder;

class MascotasSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // 1. OBTENER FUNDACIONES EXISTENTES
        // ==========================================

        // Obtener todas las fundaciones
        $todasFundaciones = Fundacion::all();

        // Obtener fundación específica de "Patitas Felices" (la del usuario)
        $fundacionPatitas = Fundacion::where('Nombre_1', 'Patitas Felices')->first();

        // ==========================================
        // 2. MASCOTAS PARA LA FUNDACIÓN "PATITAS FELICES"
        // ==========================================

        if ($fundacionPatitas) {
            // 15 mascotas para Patitas Felices
            Mascota::factory(15)
                ->conFundacion($fundacionPatitas->id)
                ->conMuchasFotos()
                ->create();

            // 3 mascotas destacadas
            Mascota::factory(3)
                ->conFundacion($fundacionPatitas->id)
                ->destacada()
                ->conVideo()
                ->create();

            // 2 cachorros
            Mascota::factory(2)
                ->conFundacion($fundacionPatitas->id)
                ->cachorro()
                ->create();
        }

        // ==========================================
        // 3. MASCOTAS PARA OTRAS FUNDACIONES
        // ==========================================

        // Cada fundación (excepto Patitas Felices) tendrá entre 3 y 8 mascotas
        foreach ($todasFundaciones as $fundacion) {
            // Saltar Patitas Felices porque ya le asignamos
            if ($fundacion->Nombre_1 === 'Patitas Felices') {
                continue;
            }

            $cantidad = rand(3, 8);
            Mascota::factory($cantidad)
                ->conFundacion($fundacion->id)
                ->create();
        }

        // ==========================================
        // 4. MASCOTAS ESPECIALES (DISTRIBUIDAS ALEATORIAMENTE)
        // ==========================================

        // Seleccionar fundaciones aleatorias para mascotas especiales
        $fundacionesRandom = $todasFundaciones->random(min(5, $todasFundaciones->count()));

        foreach ($fundacionesRandom as $fundacion) {
            // Mascotas adoptadas
            Mascota::factory(rand(1, 3))
                ->conFundacion($fundacion->id)
                ->adoptada()
                ->create();

            // Mascotas rescatadas
            Mascota::factory(rand(1, 2))
                ->conFundacion($fundacion->id)
                ->rescatada()
                ->create();

            // Mascotas con necesidades especiales
            Mascota::factory(rand(0, 1))
                ->conFundacion($fundacion->id)
                ->conNecesidadesEspeciales()
                ->create();
        }

        // ==========================================
        // 5. MASCOTAS DESTACADAS A NIVEL GENERAL
        // ==========================================

        // 5 mascotas destacadas en fundaciones aleatorias
        for ($i = 0; $i < 5; $i++) {
            $fundacionRandom = $todasFundaciones->random();
            Mascota::factory()
                ->conFundacion($fundacionRandom->id)
                ->destacada()
                ->conVideo()
                ->conMuchasFotos()
                ->create();
        }

        // ==========================================
        // 6. TOTAL DE MASCOTAS CREADAS
        // ==========================================

        $total = Mascota::count();
        $this->command->info("✅ Total de mascotas creadas: {$total}");
    }
}
