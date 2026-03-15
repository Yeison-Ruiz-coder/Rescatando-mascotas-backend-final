<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EventoController extends Controller
{
    /**
     * Listado de eventos
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date|after_or_equal:desde',
            'proximos' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = Evento::with('creadoPor');

            if ($request->filled('desde')) {
                $query->whereDate('fecha_evento', '>=', $request->desde);
            }

            if ($request->filled('hasta')) {
                $query->whereDate('fecha_evento', '<=', $request->hasta);
            }

            if ($request->boolean('proximos')) {
                $query->whereDate('fecha_evento', '>=', now());
            }

            $orden = $request->boolean('proximos') ? 'asc' : 'desc';
            $perPage = $request->get('per_page', 15);
            $eventos = $query->orderBy('fecha_evento', $orden)->paginate($perPage);

            // Estadísticas
            $estadisticas = [
                'total' => Evento::count(),
                'proximos' => Evento::whereDate('fecha_evento', '>=', now())->count(),
                'pasados' => Evento::whereDate('fecha_evento', '<', now())->count(),
                'este_mes' => Evento::whereMonth('fecha_evento', now()->month)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $eventos,
                'estadisticas' => $estadisticas,
                'message' => 'Eventos obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener eventos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los eventos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de evento
     */
    public function show($id)
    {
        try {
            $evento = Evento::with('creadoPor')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $evento,
                'message' => 'Evento obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado'
            ], 404);
        }
    }

    /**
     * Crear nuevo evento
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_evento' => 'required|string|max:255',
            'lugar_evento' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_evento' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_evento',
            'imagen_url' => 'nullable|image|max:2048',
            'capacidad_maxima' => 'nullable|integer|min:1',
            'costo' => 'nullable|numeric|min:0',
            'organizador' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:20',
            'email_contacto' => 'nullable|email|max:255',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'categoria' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $data = $request->except('imagen_url');
            $data['creado_por_id'] = auth()->id();

            // Procesar tags
            if (isset($data['tags'])) {
                $data['tags'] = json_encode($data['tags'], JSON_UNESCAPED_UNICODE);
            }

            // Subir imagen
            if ($request->hasFile('imagen_url')) {
                $path = $request->file('imagen_url')->store('eventos', 'public');
                $data['imagen_url'] = $path;
            }

            $evento = Evento::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $evento->load('creadoPor'),
                'message' => 'Evento creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear evento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar evento
     */
    public function update(Request $request, $id)
    {
        try {
            $evento = Evento::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_evento' => 'sometimes|required|string|max:255',
            'lugar_evento' => 'sometimes|required|string|max:255',
            'descripcion' => 'sometimes|required|string',
            'fecha_evento' => 'sometimes|required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_evento',
            'imagen_url' => 'nullable|image|max:2048',
            'capacidad_maxima' => 'nullable|integer|min:1',
            'costo' => 'nullable|numeric|min:0',
            'organizador' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:20',
            'email_contacto' => 'nullable|email|max:255',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'categoria' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $data = $request->except('imagen_url');

            if (isset($data['tags'])) {
                $data['tags'] = json_encode($data['tags'], JSON_UNESCAPED_UNICODE);
            }

            // Subir nueva imagen
            if ($request->hasFile('imagen_url')) {
                if ($evento->imagen_url) {
                    Storage::disk('public')->delete($evento->imagen_url);
                }
                $path = $request->file('imagen_url')->store('eventos', 'public');
                $data['imagen_url'] = $path;
            }

            $evento->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $evento->fresh('creadoPor'),
                'message' => 'Evento actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar evento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar evento
     */
    public function destroy($id)
    {
        try {
            $evento = Evento::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Evento no encontrado'
            ], 404);
        }

        DB::beginTransaction();

        try {
            if ($evento->imagen_url) {
                Storage::disk('public')->delete($evento->imagen_url);
            }

            $evento->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Evento eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar evento: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el evento',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Datos para calendario
     */
    public function calendarData()
    {
        try {
            $eventos = Evento::all()->map(function ($evento) {
                return [
                    'id' => $evento->id,
                    'title' => $evento->nombre_evento,
                    'start' => $evento->fecha_evento->format('Y-m-d'),
                    'end' => $evento->fecha_fin ? $evento->fecha_fin->format('Y-m-d') : null,
                    'description' => $evento->descripcion,
                    'location' => $evento->lugar_evento,
                    'color' => $this->getEventColor($evento),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $eventos,
                'message' => 'Datos de calendario obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener datos de calendario: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos de calendario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener color según fecha del evento
     */
    private function getEventColor($evento)
    {
        if ($evento->fecha_evento->isPast()) {
            return '#gray';
        }
        if ($evento->fecha_evento->isToday()) {
            return '#green';
        }
        if ($evento->fecha_evento->diffInDays(now()) <= 7) {
            return '#orange';
        }
        return '#blue';
    }

    /**
     * Próximos eventos
     */
    public function proximos()
    {
        try {
            $eventos = Evento::with('creadoPor')
                ->whereDate('fecha_evento', '>=', now())
                ->orderBy('fecha_evento', 'asc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $eventos,
                'message' => 'Próximos eventos obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener próximos eventos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener próximos eventos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
