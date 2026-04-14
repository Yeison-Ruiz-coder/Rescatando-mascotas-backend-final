<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::orderBy('fecha_evento', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $eventos
        ]);
    }

    public function show($id)
    {
        $evento = Evento::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $evento
        ]);
    }

    public function like($id)
    {
        $evento = Evento::findOrFail($id);
        $evento->increment('likes');

        return response()->json([
            'success' => true,
            'message' => 'Like agregado',
            'likes' => $evento->likes
        ]);
    }

    public function calendario()
    {
        $eventos = Evento::select('id', 'nombre_evento as title', 'fecha_evento as start')
            ->get();

        return response()->json($eventos);
    }
}
