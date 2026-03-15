<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reporte;
use App\Models\Rescate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReporteController extends Controller
{
    /**
     * Listado de reportes
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_reporte' => 'nullable|in:maltrato,abandono,extraviado,encontrado,otro',
            'estado' => 'nullable|in:activo,resuelto,cerrado',
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
            $query = Reporte::with(['usuario', 'resueltoPor']);

            if ($request->filled('tipo_reporte')) {
                $query->where('tipo_reporte', $request->tipo_reporte);
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            $perPage = $request->get('per_page', 20);
            $reportes = $query->latest()->paginate($perPage);

            // Estadísticas
            $estadisticas = [
                'activos' => Reporte::where('estado', 'activo')->count(),
                'resueltos' => Reporte::where('estado', 'resuelto')->count(),
                'cerrados' => Reporte::where('estado', 'cerrado')->count(),
                'total' => Reporte::count(),
                'por_tipo' => Reporte::select('tipo_reporte', DB::raw('count(*) as total'))
                    ->groupBy('tipo_reporte')
                    ->get(),
                'este_mes' => Reporte::whereMonth('created_at', now()->month)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $reportes,
                'estadisticas' => $estadisticas,
                'message' => 'Reportes obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener reportes: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los reportes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de reporte
     */
    public function show($id)
    {
        try {
            $reporte = Reporte::with(['usuario', 'resueltoPor'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $reporte,
                'message' => 'Reporte obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reporte no encontrado'
            ], 404);
        }
    }

    /**
     * Crear nuevo reporte (admin)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_reporte' => 'required|in:maltrato,abandono,extraviado,encontrado,otro',
            'descripcion' => 'required|string|min:10',
            'ubicacion' => 'required|string|max:255',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'user_id' => 'required|exists:users,id',
            'foto_url' => 'nullable|image|max:2048',
            'contacto_telefono' => 'nullable|string|max:20',
            'contacto_email' => 'nullable|email|max:255',
            'datos_animal' => 'nullable|array',
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
            $data = $request->except('foto_url');

            // Procesar datos del animal
            if (isset($data['datos_animal'])) {
                $data['datos_animal'] = json_encode($data['datos_animal'], JSON_UNESCAPED_UNICODE);
            }

            // Subir foto
            if ($request->hasFile('foto_url')) {
                $path = $request->file('foto_url')->store('reportes', 'public');
                $data['foto_url'] = $path;
            }

            $reporte = Reporte::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $reporte->load('usuario'),
                'message' => 'Reporte creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear reporte: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el reporte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar reporte
     */
    public function update(Request $request, $id)
    {
        try {
            $reporte = Reporte::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reporte no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'sometimes|required|in:activo,resuelto,cerrado',
            'solucion' => 'nullable|string',
            'notas_internas' => 'nullable|string',
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
            $updateData = [
                'estado' => $request->estado ?? $reporte->estado,
                'solucion' => $request->solucion,
                'notas_internas' => $request->notas_internas,
            ];

            if ($request->estado === 'resuelto') {
                $updateData['resuelto_por'] = auth()->id();
                $updateData['fecha_resolucion'] = now();
            }

            $reporte->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $reporte->fresh(['usuario', 'resueltoPor']),
                'message' => 'Reporte actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar reporte: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el reporte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar reporte
     */
    public function destroy($id)
    {
        try {
            $reporte = Reporte::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reporte no encontrado'
            ], 404);
        }

        DB::beginTransaction();

        try {
            if ($reporte->foto_url) {
                Storage::disk('public')->delete($reporte->foto_url);
            }

            $reporte->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Reporte eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar reporte: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el reporte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convertir reporte en rescate
     */
    public function convertirARescate(Request $request, $id)
    {
        try {
            $reporte = Reporte::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reporte no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'fecha_rescate' => 'required|date',
            'lugar_rescate' => 'required|string|max:255',
            'descripcion_rescate' => 'required|string',
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
            $rescate = Rescate::create([
                'fecha_rescate' => $request->fecha_rescate,
                'lugar_rescate' => $request->lugar_rescate,
                'descripcion_rescate' => $request->descripcion_rescate,
                'estado' => 'en_proceso',
                'reporte_id' => $reporte->id,
                'usuario_reporto_id' => $reporte->user_id,
                'administrador_gestion_id' => auth()->id(),
            ]);

            $reporte->update([
                'estado' => 'resuelto',
                'resuelto_por' => auth()->id(),
                'fecha_resolucion' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'reporte' => $reporte->fresh(),
                    'rescate' => $rescate->load('usuarioReporto'),
                ],
                'message' => 'Rescate creado a partir del reporte exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al convertir reporte a rescate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear rescate',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estadísticas generales de reportes
     */
    public function estadisticas()
    {
        try {
            $reportesPorTipo = Reporte::select('tipo_reporte', DB::raw('count(*) as total'))
                ->groupBy('tipo_reporte')
                ->get();

            $reportesPorMes = Reporte::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(*) as total')
            )
                ->where('created_at', '>=', now()->subYear())
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            $tiempoResolucion = Reporte::whereNotNull('fecha_resolucion')
                ->select(DB::raw('AVG(DATEDIFF(fecha_resolucion, created_at)) as promedio_dias'))
                ->first();

            $topUbicaciones = Reporte::select('ubicacion', DB::raw('count(*) as total'))
                ->whereNotNull('ubicacion')
                ->groupBy('ubicacion')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'por_tipo' => $reportesPorTipo,
                    'por_mes' => $reportesPorMes,
                    'tiempo_promedio_resolucion' => round($tiempoResolucion->promedio_dias ?? 0, 1),
                    'top_ubicaciones' => $topUbicaciones,
                    'totales' => [
                        'activos' => Reporte::where('estado', 'activo')->count(),
                        'resueltos' => Reporte::where('estado', 'resuelto')->count(),
                        'cerrados' => Reporte::where('estado', 'cerrado')->count(),
                    ],
                ],
                'message' => 'Estadísticas obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener estadísticas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reportes cercanos (por ubicación)
     */
    public function cercanos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'radio' => 'nullable|integer|min:1|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $lat = $request->latitud;
            $lng = $request->longitud;
            $radio = $request->get('radio', 10); // km

            // Filtro aproximado por diferencia de coordenadas (simplificado)
            $reportes = Reporte::where('estado', 'activo')
                ->whereNotNull('latitud')
                ->whereNotNull('longitud')
                ->whereBetween('latitud', [$lat - 0.5, $lat + 0.5])
                ->whereBetween('longitud', [$lng - 0.5, $lng + 0.5])
                ->with('usuario')
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $reportes,
                'message' => 'Reportes cercanos obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener reportes cercanos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener reportes cercanos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
