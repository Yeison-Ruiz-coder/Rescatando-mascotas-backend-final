<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CategoriaController extends Controller
{
    /**
     * Listado de categorías
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            $query = Categoria::withCount('productos');

            if ($request->filled('search')) {
                $query->where('nombre', 'like', "%{$request->search}%");
            }

            $perPage = $request->get('per_page', 20);
            $categorias = $query->orderBy('nombre')->paginate($perPage);

            // Estadísticas
            $stats = [
                'total' => Categoria::count(),
                'con_productos' => Categoria::has('productos')->count(),
                'sin_productos' => Categoria::doesntHave('productos')->count(),
                'total_productos' => Categoria::withCount('productos')->get()->sum('productos_count'),
            ];

            return response()->json([
                'success' => true,
                'data' => $categorias,
                'estadisticas' => $stats,
                'message' => 'Categorías obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener categorías: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las categorías',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de categoría
     */
    public function show($id)
    {
        try {
            $categoria = Categoria::with(['productos', 'categoriasHijas', 'categoriaPadre'])
                ->withCount('productos')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $categoria,
                'message' => 'Categoría obtenida exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada'
            ], 404);
        }
    }

    /**
     * Crear nueva categoría
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:categorias',
            'descripcion' => 'nullable|string',
            'categoria_padre_id' => 'nullable|exists:categorias,id',
            'activo' => 'boolean',
            'orden' => 'nullable|integer|min:0',
            'imagen_url' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $categoria = Categoria::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $categoria,
                'message' => 'Categoría creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al crear categoría: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear la categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar categoría
     */
    public function update(Request $request, $id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255|unique:categorias,nombre,' . $id,
            'descripcion' => 'nullable|string',
            'categoria_padre_id' => 'nullable|exists:categorias,id',
            'activo' => 'boolean',
            'orden' => 'nullable|integer|min:0',
            'imagen_url' => 'nullable|url|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Evitar que se asigne a sí misma como padre
        if ($request->categoria_padre_id == $id) {
            return response()->json([
                'success' => false,
                'message' => 'Una categoría no puede ser padre de sí misma'
            ], 422);
        }

        try {
            $categoria->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $categoria,
                'message' => 'Categoría actualizada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar categoría: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar categoría
     */
    public function destroy($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada'
            ], 404);
        }

        try {
            // Verificar si tiene productos
            if ($categoria->productos()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la categoría porque tiene productos asociados'
                ], 422);
            }

            // Verificar si tiene subcategorías
            if ($categoria->categoriasHijas()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar la categoría porque tiene subcategorías asociadas'
                ], 422);
            }

            $categoria->delete();

            return response()->json([
                'success' => true,
                'message' => 'Categoría eliminada exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar categoría: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado activo/inactivo
     */
    public function toggleActivo($id)
    {
        try {
            $categoria = Categoria::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Categoría no encontrada'
            ], 404);
        }

        try {
            $categoria->update([
                'activo' => !$categoria->activo
            ]);

            return response()->json([
                'success' => true,
                'data' => ['activo' => $categoria->activo],
                'message' => 'Estado actualizado correctamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al cambiar estado: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Árbol de categorías
     */
    public function arbol()
    {
        try {
            $categorias = Categoria::with(['categoriasHijas' => function($q) {
                $q->withCount('productos');
            }])
            ->withCount('productos')
            ->whereNull('categoria_padre_id')
            ->orderBy('nombre')
            ->get();

            return response()->json([
                'success' => true,
                'data' => $categorias,
                'message' => 'Árbol de categorías obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener árbol de categorías: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el árbol de categorías',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Categorías para select (jerárquicas)
     */
    public function paraSelect()
    {
        try {
            $categorias = Categoria::whereNull('categoria_padre_id')
                ->with(['categoriasHijas' => function($q) {
                    $q->orderBy('nombre');
                }])
                ->orderBy('nombre')
                ->get()
                ->map(function($categoria) {
                    $result = [
                        'id' => $categoria->id,
                        'nombre' => $categoria->nombre,
                        'productos_count' => $categoria->productos_count,
                    ];

                    if ($categoria->categoriasHijas->isNotEmpty()) {
                        $result['hijas'] = $categoria->categoriasHijas->map(function($hija) {
                            return [
                                'id' => $hija->id,
                                'nombre' => $hija->nombre,
                                'productos_count' => $hija->productos_count,
                            ];
                        });
                    }

                    return $result;
                });

            return response()->json([
                'success' => true,
                'data' => $categorias,
                'message' => 'Categorías obtenidas exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener categorías para select: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener categorías',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
