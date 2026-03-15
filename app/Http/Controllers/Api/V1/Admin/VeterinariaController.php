<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Veterinaria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class VeterinariaController extends Controller
{
    /**
     * Listado de veterinarias
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'nullable|string|max:100',
            'urgencias_24h' => 'nullable|boolean',
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
            $query = Veterinaria::query();

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('Nombre_vet', 'like', "%{$search}%")
                      ->orWhere('Email', 'like', "%{$search}%")
                      ->orWhere('Telefono', 'like', "%{$search}%")
                      ->orWhere('Direccion', 'like', "%{$search}%");
                });
            }

            if ($request->filled('urgencias_24h')) {
                $query->where('urgencias_24h', $request->urgencias_24h);
            }

            $perPage = $request->get('per_page', 15);
            $veterinarias = $query->orderBy('Nombre_vet')->paginate($perPage);

            // Estadísticas
            $estadisticas = [
                'total' => Veterinaria::count(),
                'urgencias_24h' => Veterinaria::where('urgencias_24h', true)->count(),
                'servicios_comunes' => $this->obtenerServiciosComunes(),
            ];

            return response()->json([
                'success' => true,
                'data' => $veterinarias,
                'estadisticas' => $estadisticas,
                'message' => 'Veterinarias obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener veterinarias: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las veterinarias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de veterinaria
     */
    public function show($id)
    {
        try {
            $veterinaria = Veterinaria::with(['rescates', 'historialMedico'])->findOrFail($id);

            // Decodificar JSON
            $servicios = json_decode($veterinaria->servicios, true) ?? [];
            $convenios = json_decode($veterinaria->convenios, true) ?? [];

            // Estadísticas
            $stats = [
                'rescates_atendidos' => $veterinaria->rescates()->count(),
                'consultas_realizadas' => $veterinaria->historialMedico()->count(),
                'ultimo_rescate' => $veterinaria->rescates()->latest()->first()?->fecha_rescate,
                'total_mascotas_atendidas' => $veterinaria->historialMedico()->distinct('mascota_id')->count('mascota_id'),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'veterinaria' => $veterinaria,
                    'servicios' => $servicios,
                    'convenios' => $convenios,
                    'estadisticas' => $stats,
                ],
                'message' => 'Veterinaria obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Veterinaria no encontrada'
            ], 404);
        }
    }

    /**
     * Crear nueva veterinaria
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Nombre_vet' => 'required|string|max:255',
            'Direccion' => 'required|string|unique:veterinarias',
            'Telefono' => 'required|string|unique:veterinarias',
            'Email' => 'required|email|unique:veterinarias',
            'servicios' => 'nullable|array',
            'urgencias_24h' => 'boolean',
            'convenios' => 'nullable|array',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'sitio_web' => 'nullable|url|max:255',
            'horario_atencion' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'logo_url' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();

            // Convertir arrays a JSON
            if (isset($data['servicios'])) {
                $data['servicios'] = json_encode($data['servicios'], JSON_UNESCAPED_UNICODE);
            }

            if (isset($data['convenios'])) {
                $data['convenios'] = json_encode($data['convenios'], JSON_UNESCAPED_UNICODE);
            }

            $veterinaria = Veterinaria::create($data);

            return response()->json([
                'success' => true,
                'data' => $veterinaria,
                'message' => 'Veterinaria creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear veterinaria: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la veterinaria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar veterinaria
     */
    public function update(Request $request, $id)
    {
        try {
            $veterinaria = Veterinaria::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Veterinaria no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'Nombre_vet' => 'sometimes|required|string|max:255',
            'Direccion' => 'sometimes|required|string|unique:veterinarias,Direccion,' . $id,
            'Telefono' => 'sometimes|required|string|unique:veterinarias,Telefono,' . $id,
            'Email' => 'sometimes|required|email|unique:veterinarias,Email,' . $id,
            'servicios' => 'nullable|array',
            'urgencias_24h' => 'boolean',
            'convenios' => 'nullable|array',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'sitio_web' => 'nullable|url|max:255',
            'horario_atencion' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'logo_url' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();

            if (isset($data['servicios'])) {
                $data['servicios'] = json_encode($data['servicios'], JSON_UNESCAPED_UNICODE);
            }

            if (isset($data['convenios'])) {
                $data['convenios'] = json_encode($data['convenios'], JSON_UNESCAPED_UNICODE);
            }

            $veterinaria->update($data);

            return response()->json([
                'success' => true,
                'data' => $veterinaria,
                'message' => 'Veterinaria actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar veterinaria: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la veterinaria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar veterinaria
     */
    public function destroy($id)
    {
        try {
            $veterinaria = Veterinaria::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Veterinaria no encontrada'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Verificar relaciones
            if ($veterinaria->rescates()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la veterinaria porque tiene rescates asociados'
                ], 422);
            }

            if ($veterinaria->historialMedico()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la veterinaria porque tiene historial médico asociado'
                ], 422);
            }

            $veterinaria->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Veterinaria eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar veterinaria: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la veterinaria',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener servicios comunes (estadística)
     */
    private function obtenerServiciosComunes()
    {
        $todosServicios = [];
        $veterinarias = Veterinaria::whereNotNull('servicios')->get(['servicios']);

        foreach ($veterinarias as $vet) {
            $servicios = json_decode($vet->servicios, true) ?? [];
            $todosServicios = array_merge($todosServicios, $servicios);
        }

        $conteo = array_count_values($todosServicios);
        arsort($conteo);

        return array_slice($conteo, 0, 5, true);
    }

    /**
     * Veterinarias cercanas (por coordenadas)
     */
    public function cercanas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'radio' => 'nullable|integer|min:1|max:100',
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

            $veterinarias = Veterinaria::selectRaw(
                "*, (6371 * acos(cos(radians(?)) * cos(radians(latitud)) * cos(radians(longitud) - radians(?)) + sin(radians(?)) * sin(radians(latitud)))) AS distancia",
                [$lat, $lng, $lat]
            )
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->having('distancia', '<=', $radio)
            ->orderBy('distancia')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $veterinarias,
                'message' => 'Veterinarias cercanas obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener veterinarias cercanas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener veterinarias cercanas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
