<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Raza;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RazaController extends Controller
{
    /**
     * Listado de razas
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'especie' => 'nullable|string|max:100',
            'search' => 'nullable|string|max:100',
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
            $query = Raza::query();

            if ($request->filled('especie')) {
                $query->where('especie', $request->especie);
            }

            if ($request->filled('search')) {
                $query->where('nombre_raza', 'like', "%{$request->search}%");
            }

            $perPage = $request->get('per_page', 20);
            $razas = $query->orderBy('especie')->orderBy('nombre_raza')->paginate($perPage);

            // Estadísticas por especie
            $stats = [
                'total' => Raza::count(),
                'perros' => Raza::where('especie', 'Perro')->count(),
                'gatos' => Raza::where('especie', 'Gato')->count(),
                'otros' => Raza::whereNotIn('especie', ['Perro', 'Gato'])->orWhereNull('especie')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $razas,
                'estadisticas' => $stats,
                'message' => 'Razas obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener razas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las razas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de raza
     */
    public function show($id)
    {
        try {
            $raza = Raza::with('mascotas')->findOrFail($id);

            // Estadísticas de mascotas por raza
            $totalMascotas = $raza->mascotas->count();
            $mascotasAdoptadas = $raza->mascotas->where('estado', 'Adoptado')->count();
            $mascotasDisponibles = $raza->mascotas->where('estado', 'En adopcion')->count();
            $mascotasRescatadas = $raza->mascotas->where('estado', 'Rescatada')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'raza' => $raza,
                    'estadisticas' => [
                        'total_mascotas' => $totalMascotas,
                        'adoptadas' => $mascotasAdoptadas,
                        'disponibles' => $mascotasDisponibles,
                        'rescatadas' => $mascotasRescatadas,
                    ],
                ],
                'message' => 'Raza obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Raza no encontrada'
            ], 404);
        }
    }

    /**
     * Crear nueva raza
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_raza' => 'required|string|max:255|unique:razas',
            'especie' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string',
            'tamanio' => 'nullable|in:pequeño,mediano,grande',
            'esperanza_vida' => 'nullable|integer|min:1|max:30',
            'pelaje' => 'nullable|string|max:100',
            'origen' => 'nullable|string|max:255',
            'cuidados_especiales' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $raza = Raza::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $raza,
                'message' => 'Raza creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear raza: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la raza',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar raza
     */
    public function update(Request $request, $id)
    {
        try {
            $raza = Raza::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Raza no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_raza' => 'sometimes|required|string|max:255|unique:razas,nombre_raza,' . $id,
            'especie' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string',
            'tamanio' => 'nullable|in:pequeño,mediano,grande',
            'esperanza_vida' => 'nullable|integer|min:1|max:30',
            'pelaje' => 'nullable|string|max:100',
            'origen' => 'nullable|string|max:255',
            'cuidados_especiales' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $raza->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $raza,
                'message' => 'Raza actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar raza: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la raza',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar raza
     */
    public function destroy($id)
    {
        try {
            $raza = Raza::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Raza no encontrada'
            ], 404);
        }

        try {
            // Verificar si tiene mascotas asociadas
            if ($raza->mascotas()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la raza porque tiene mascotas asociadas'
                ], 422);
            }

            $raza->delete();

            return response()->json([
                'success' => true,
                'message' => 'Raza eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar raza: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la raza',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener razas por especie
     */
    public function porEspecie($especie)
    {
        try {
            $razas = Raza::where('especie', $especie)
                ->orderBy('nombre_raza')
                ->get(['id', 'nombre_raza']);

            return response()->json([
                'success' => true,
                'data' => $razas,
                'message' => 'Razas obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener razas por especie: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener razas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener todas las especies disponibles
     */
    public function especies()
    {
        try {
            $especies = Raza::whereNotNull('especie')
                ->distinct('especie')
                ->pluck('especie')
                ->filter()
                ->values();

            return response()->json([
                'success' => true,
                'data' => $especies,
                'message' => 'Especies obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener especies: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener especies',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
