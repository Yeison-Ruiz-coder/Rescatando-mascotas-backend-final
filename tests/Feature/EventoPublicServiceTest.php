<?php

namespace Tests\Feature;

use App\Models\Evento;
use App\Services\Public\EventoPublicService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EventoPublicServiceTest extends TestCase
{
    public function test_get_calendar_data_uses_limited_columns(): void
    {
        Evento::create([
            'nombre_evento' => 'Campaña de adopción',
            'lugar_evento' => 'Parque Central',
            'descripcion' => 'Evento de prueba',
            'fecha_evento' => now()->addDay(),
            'fecha_fin' => now()->addDay()->addHour(),
            'imagen_url' => 'https://example.test/evento.jpg',
            'fundacion_id' => null,
            'tipo' => 'fundacion',
            'likes' => 0,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();

        (new EventoPublicService())->getCalendarData();

        $query = collect(DB::getQueryLog())->last(fn ($entry) => str_contains($entry['query'], 'from `eventos`'));

        $this->assertNotNull($query);
        $this->assertStringNotContainsString('select *', strtolower($query['query']));
        $this->assertStringContainsString('nombre_evento', $query['query']);
        $this->assertStringContainsString('fecha_evento', $query['query']);
        $this->assertStringContainsString('imagen_url', $query['query']);
    }
}
