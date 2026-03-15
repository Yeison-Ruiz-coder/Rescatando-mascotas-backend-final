<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tienda;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TiendaController extends Controller
{
    /**
     * Listado de tiendas
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'nullable|in:veterinaria,fundacion',
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
            $query = Tienda::with('vendedor');

            if ($request->filled('tipo')) {
                $query->where('tipo', $request->tipo);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('direccion', 'like', "%{$search}%")
                      ->orWhere('telefono', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 15);
            $tiendas = $query->orderBy('nombre')->paginate($perPage);

            // Estadísticas
            $stats = [
                'total' => Tienda::count(),
                'veterinarias' => Tienda::where('tipo', 'veterinaria')->count(),
                'fundaciones' => Tienda::where('tipo', 'fundacion')->count(),
                'total_productos' => Tienda::withCount('productos')->get()->sum('productos_count'),
            ];

            return response()->json([
                'success' => true,
                'data' => $tiendas,
                'estadisticas' => $stats,
                'message' => 'Tiendas obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener tiendas: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las tiendas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de tienda
     */
    public function show($id)
    {
        try {
            $tienda = Tienda::with(['vendedor', 'productos' => function($q) {
                $q->where('estado', 'disponible');
            }])->findOrFail($id);

            // Estadísticas de la tienda
            $stats = [
                'total_productos' => $tienda->productos->count(),
                'valor_inventario' => $tienda->productos->sum(function($p) {
                    return $p->precio * $p->stock;
                }),
                'stock_total' => $tienda->productos->sum('stock'),
                'productos_stock_bajo' => $tienda->productos->where('stock', '<=', 5)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'tienda' => $tienda,
                    'estadisticas' => $stats,
                ],
                'message' => 'Tienda obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tienda no encontrada'
            ], 404);
        }
    }

    /**
     * Crear nueva tienda
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'direccion' => 'required|string|unique:tiendas',
            'telefono' => 'required|string|unique:tiendas',
            'email' => 'required|email|unique:tiendas',
            'tipo' => 'required|in:veterinaria,fundacion',
            'user_id' => 'required|exists:users,id',
            'descripcion' => 'nullable|string',
            'horario' => 'nullable|string',
            'logo_url' => 'nullable|url|max:255',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'sitio_web' => 'nullable|url|max:255',
            'redes_sociales' => 'nullable|array',
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
            $data = $request->all();

            if (isset($data['redes_sociales'])) {
                $data['redes_sociales'] = json_encode($data['redes_sociales'], JSON_UNESCAPED_UNICODE);
            }

            $tienda = Tienda::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $tienda->load('vendedor'),
                'message' => 'Tienda creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear tienda: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la tienda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar tienda
     */
    public function update(Request $request, $id)
    {
        try {
            $tienda = Tienda::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tienda no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'direccion' => 'sometimes|required|string|unique:tiendas,direccion,' . $id,
            'telefono' => 'sometimes|required|string|unique:tiendas,telefono,' . $id,
            'email' => 'sometimes|required|email|unique:tiendas,email,' . $id,
            'tipo' => 'sometimes|required|in:veterinaria,fundacion',
            'user_id' => 'sometimes|required|exists:users,id',
            'descripcion' => 'nullable|string',
            'horario' => 'nullable|string',
            'logo_url' => 'nullable|url|max:255',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'sitio_web' => 'nullable|url|max:255',
            'redes_sociales' => 'nullable|array',
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

            if (isset($data['redes_sociales'])) {
                $data['redes_sociales'] = json_encode($data['redes_sociales'], JSON_UNESCAPED_UNICODE);
            }

            $tienda->update($data);

            return response()->json([
                'success' => true,
                'data' => $tienda->fresh('vendedor'),
                'message' => 'Tienda actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar tienda: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la tienda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar tienda
     */
    public function destroy($id)
    {
        try {
            $tienda = Tienda::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tienda no encontrada'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Verificar si tiene productos
            if ($tienda->productos()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la tienda porque tiene productos asociados'
                ], 422);
            }

            $tienda->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tienda eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar tienda: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la tienda',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Productos de la tienda
     */
    public function productos($id, Request $request)
    {
        try {
            $tienda = Tienda::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tienda no encontrada'
            ], 404);
        }

        try {
            $query = $tienda->productos();

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->boolean('stock_bajo')) {
                $query->where('stock', '<=', 5);
            }

            $perPage = $request->get('per_page', 15);
            $productos = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $productos,
                'message' => 'Productos de la tienda obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener productos de tienda: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
