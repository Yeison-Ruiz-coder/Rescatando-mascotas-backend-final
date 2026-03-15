<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    /**
     * Listado de eventos
     */
    public function index(Request $request)
    {
        $query = Evento::where('fecha_evento', '>=', now());

        if ($request->has('mes')) {
            $query->whereMonth('fecha_evento', $request->mes);
        }

        if ($request->has('anio')) {
            $query->whereYear('fecha_evento', $request->anio);
        }

        $eventos = $query->orderBy('fecha_evento', 'asc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $eventos
        ]);
    }

    /**
     * Detalle de evento
     */
    public function show($id)
    {
        $evento = Evento::with('creadoPor')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $evento
        ]);
    }

    /**
     * Datos para calendario
     */
    public function calendario()
    {
        $eventos = Evento::where('fecha_evento', '>=', now())
            ->orderBy('fecha_evento', 'asc')
            ->get()
            ->map(function ($evento) {
                return [
                    'id' => $evento->id,
                    'title' => $evento->nombre_evento,
                    'start' => $evento->fecha_evento,
                    'description' => $evento->descripcion,
                    'location' => $evento->lugar_evento,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $eventos
        ]);
    }
}
