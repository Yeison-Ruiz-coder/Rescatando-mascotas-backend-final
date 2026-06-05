<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Models\Fundacion;
use App\Models\Mascota;
use App\Models\Veterinaria;
use App\Services\Public\EventoPublicService;
use App\Services\Public\FundacionPublicService;
use App\Services\Public\MascotaPublicService;
use App\Services\Public\VeterinariaPublicService;
use Tests\TestCase;

class PublicSearchFiltersTest extends TestCase
{
    public function test_public_search_filters_work_for_mascotas_and_fundaciones(): void
    {
        $suffix = uniqid('search-', true);

        Fundacion::create([
            'Nombre_1' => 'Fundación Busca Vida ' . $suffix,
            'Direccion' => 'Calle ' . $suffix,
            'Telefono' => '111' . $suffix,
            'Email' => 'buscavida' . $suffix . '@example.test',
            'ciudad' => 'Bogotá',
            'verificado' => true,
            'recibe_voluntarios' => true,
        ]);

        Mascota::create([
            'nombre_mascota' => 'Luna Busca',
            'descripcion' => 'Mascota para búsqueda de adopción',
            'especie' => 'Perro',
            'genero' => 'Hembra',
            'tamano' => 'Mediano',
            'estado' => 'En adopcion',
            'fundacion_id' => Fundacion::first()->id,
            'destacada' => false,
        ]);

        $mascotas = (new MascotaPublicService())->getAll(['buscar' => 'Luna'], 15);
        $fundaciones = (new FundacionPublicService())->getAll(['buscar' => 'Busca Vida'], 15);

        $this->assertTrue($mascotas->total() >= 1);
        $this->assertTrue($fundaciones->total() >= 1);
    }

    public function test_public_search_filters_work_for_veterinarias_and_eventos(): void
    {
        $suffix = uniqid('search-vet-', true);

        Veterinaria::create([
            'Nombre_vet' => 'Clínica Vet Buscar ' . $suffix,
            'Direccion' => 'Cra ' . $suffix,
            'Telefono' => '555' . $suffix,
            'Email' => 'buscar' . $suffix . '@example.test',
            'ciudad' => 'Medellín',
            'descripcion' => 'Atención integral para mascotas',
            'servicios' => ['consulta'],
            'urgencias_24h' => true,
            'verificado' => true,
        ]);

        $eventSuffix = uniqid('event-search-', true);

        Evento::create([
            'nombre_evento' => 'Taller de búsqueda ' . $eventSuffix,
            'lugar_evento' => 'Parque Central ' . $eventSuffix,
            'descripcion' => 'Evento para encontrar adopciones',
            'fecha_evento' => now()->addDay(),
            'fecha_fin' => now()->addDay()->addHour(),
            'imagen_url' => 'https://example.test/evento.jpg',
            'fundacion_id' => null,
            'tipo' => 'fundacion',
            'likes' => 0,
        ]);

        Veterinaria::create([
            'Nombre_vet' => 'Clínica sin coincidencia ' . $suffix,
            'Direccion' => 'Cra 9 ' . $suffix,
            'Telefono' => '666' . $suffix,
            'Email' => 'otra' . $suffix . '@example.test',
            'ciudad' => 'Cali',
            'descripcion' => 'Otra clínica',
            'servicios' => ['desparasitación'],
            'urgencias_24h' => false,
            'verificado' => true,
        ]);

        Evento::create([
            'nombre_evento' => 'Campaña sin coincidencia ' . $eventSuffix,
            'lugar_evento' => 'Auditorio Norte ' . $eventSuffix,
            'descripcion' => 'Evento de otra temática',
            'fecha_evento' => now()->addDays(2),
            'fecha_fin' => now()->addDays(2)->addHour(),
            'imagen_url' => 'https://example.test/otro.jpg',
            'fundacion_id' => null,
            'tipo' => 'fundacion',
            'likes' => 0,
        ]);

        $veterinarias = (new VeterinariaPublicService())->getAll(['buscar' => 'Buscar ' . $suffix], 15);
        $eventos = (new EventoPublicService())->getAll(['buscar' => 'búsqueda ' . $eventSuffix], 15);

        $this->assertSame(1, $veterinarias->total());
        $this->assertStringContainsString('Clínica Vet Buscar', $veterinarias->first()->Nombre_vet);
        $this->assertSame(1, $eventos->total());
        $this->assertStringContainsString('Taller de búsqueda', $eventos->first()->nombre_evento);
    }
}
