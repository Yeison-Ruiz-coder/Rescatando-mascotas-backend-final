<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventoController extends Controller
{
    /**
     * Display a listing of all public events.
     */
    public function index()
    {
        $eventos = Evento::orderBy('fecha_evento', 'asc')->get();

        // Agregar información de asistencia para el usuario autenticado
        if (auth()->check()) {
            foreach ($eventos as $evento) {
                $evento->usuario_confirmado = $evento->usuarioConfirmoAsistencia(auth()->id());
            }
        }

        // Agregar contador de asistentes a cada evento
        $eventos->each(function($evento) {
            $evento->total_asistentes = $evento->total_asistentes;
        });

        return response()->json([
            'success' => true,
            'data' => $eventos
        ]);
    }

    /**
     * Display the specified event.
     */
    public function show($id)
    {
        $evento = Evento::with(['asistentes' => function($query) {
                $query->limit(10);
            }])
            ->findOrFail($id);

        // Agregar información de asistencia para el usuario autenticado
        if (auth()->check()) {
            $evento->usuario_confirmado = $evento->usuarioConfirmoAsistencia(auth()->id());
        }

        $evento->total_asistentes = $evento->total_asistentes;

        return response()->json([
            'success' => true,
            'data' => $evento
        ]);
    }

    /**
     * Add like to an event.
     */
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

    /**
     * Get calendar data for events.
     */
    public function calendario()
    {
        $eventos = Evento::select('id', 'nombre_evento as title', 'fecha_evento as start')
            ->get();

        return response()->json($eventos);
    }

    /**
     * Confirm user attendance to an event.
     */
    public function confirmarAsistencia($id)
    {
        // Verificar si el usuario está autenticado
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión para confirmar asistencia'
            ], 401);
        }

        $evento = Evento::findOrFail($id);
        $userId = auth()->id();

        // Verificar si ya confirmó asistencia
        if ($evento->usuarioConfirmoAsistencia($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'Ya has confirmado asistencia a este evento'
            ], 400);
        }

        // Agregar asistente
        $evento->asistentes()->attach($userId, ['estado' => 'confirmado']);

        return response()->json([
            'success' => true,
            'message' => 'Asistencia confirmada exitosamente',
            'total_asistentes' => $evento->total_asistentes
        ]);
    }

    /**
     * Cancel user attendance to an event.
     */
    public function cancelarAsistencia($id)
    {
        // Verificar si el usuario está autenticado
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión para cancelar asistencia'
            ], 401);
        }

        $evento = Evento::findOrFail($id);
        $userId = auth()->id();

        // Verificar si confirmó asistencia
        if (!$evento->usuarioConfirmoAsistencia($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'No habías confirmado asistencia a este evento'
            ], 400);
        }

        // Eliminar asistente
        $evento->asistentes()->detach($userId);

        return response()->json([
            'success' => true,
            'message' => 'Asistencia cancelada',
            'total_asistentes' => $evento->total_asistentes
        ]);
    }

    /**
     * Get events by type (admin or fundacion)
     */
    public function porTipo($tipo)
    {
        $eventos = Evento::where('tipo', $tipo)
            ->orderBy('fecha_evento', 'asc')
            ->get();

        if (auth()->check()) {
            foreach ($eventos as $evento) {
                $evento->usuario_confirmado = $evento->usuarioConfirmoAsistencia(auth()->id());
            }
        }

        $eventos->each(function($evento) {
            $evento->total_asistentes = $evento->total_asistentes;
        });

        return response()->json([
            'success' => true,
            'data' => $eventos
        ]);
    }

    /**
     * Get upcoming events (future events only)
     */
    public function proximos()
    {
        $eventos = Evento::where('fecha_evento', '>', now())
            ->orderBy('fecha_evento', 'asc')
            ->get();

        if (auth()->check()) {
            foreach ($eventos as $evento) {
                $evento->usuario_confirmado = $evento->usuarioConfirmoAsistencia(auth()->id());
            }
        }

        $eventos->each(function($evento) {
            $evento->total_asistentes = $evento->total_asistentes;
        });

        return response()->json([
            'success' => true,
            'data' => $eventos
        ]);
    }

    /**
     * Get events that the authenticated user is attending
     */
    public function misEventos()
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes iniciar sesión'
            ], 401);
        }

        $user = auth()->user();
        $eventos = $user->eventosAsistencia()
            ->orderBy('fecha_evento', 'asc')
            ->get();

        foreach ($eventos as $evento) {
            $evento->usuario_confirmado = true;
            $evento->total_asistentes = $evento->total_asistentes;
        }

        return response()->json([
            'success' => true,
            'data' => $eventos
        ]);
    }
}
