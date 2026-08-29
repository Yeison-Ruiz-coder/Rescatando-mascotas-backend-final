<?php

namespace Database\Seeders;

use App\Models\Mascota;
use App\Models\Fundacion;
use Database\Factories\MascotaFactory;
use Illuminate\Database\Seeder;

class MascotasSeeder extends Seeder
{
    public function run(): void
    {
        MascotaFactory::resetImagenesUsadas();
        $stockTotal = count(MascotaFactory::imagenesDisponibles());

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
            $stockDisponible = max(0, $stockTotal - count(MascotaFactory::imagenesUsadas()));

            // 15 mascotas para Patitas Felices
            Mascota::factory(min(15, $stockDisponible))
                ->conFundacion($fundacionPatitas->id)
                ->conMuchasFotos()
                ->create();

            $stockDisponible = max(0, $stockTotal - count(MascotaFactory::imagenesUsadas()));

            // 3 mascotas destacadas
            Mascota::factory(min(3, $stockDisponible))
                ->conFundacion($fundacionPatitas->id)
                ->destacada()
                ->conVideo()
                ->create();

            $stockDisponible = max(0, $stockTotal - count(MascotaFactory::imagenesUsadas()));

            // 2 cachorros
            Mascota::factory(min(2, $stockDisponible))
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

            $stockDisponible = max(0, $stockTotal - count(MascotaFactory::imagenesUsadas()));
            if ($stockDisponible <= 0) {
                break;
            }

            $cantidad = min(rand(3, 8), $stockDisponible);
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
            $stockDisponible = max(0, $stockTotal - count(MascotaFactory::imagenesUsadas()));
            if ($stockDisponible <= 0) {
                break;
            }

            // Mascotas adoptadas
            Mascota::factory(min(rand(1, 3), $stockDisponible))
                ->conFundacion($fundacion->id)
                ->adoptada()
                ->create();

            $stockDisponible = max(0, $stockTotal - count(MascotaFactory::imagenesUsadas()));
            if ($stockDisponible <= 0) {
                break;
            }

            // Mascotas rescatadas
            Mascota::factory(min(rand(1, 2), $stockDisponible))
                ->conFundacion($fundacion->id)
                ->rescatada()
                ->create();

            $stockDisponible = max(0, $stockTotal - count(MascotaFactory::imagenesUsadas()));
            if ($stockDisponible <= 0) {
                break;
            }

            // Mascotas con necesidades especiales
            Mascota::factory(min(rand(0, 1), $stockDisponible))
                ->conFundacion($fundacion->id)
                ->conNecesidadesEspeciales()
                ->create();
        }

        // ==========================================
        // 5. MASCOTAS DESTACADAS A NIVEL GENERAL
        // ==========================================

        // 5 mascotas destacadas en fundaciones aleatorias
        for ($i = 0; $i < 5; $i++) {
            $stockDisponible = max(0, $stockTotal - count(MascotaFactory::imagenesUsadas()));
            if ($stockDisponible <= 0) {
                break;
            }

            $fundacionRandom = $todasFundaciones->random();
            Mascota::factory(min(1, $stockDisponible))
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
