<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    /**
     * Listado de productos
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'nullable|exists:users,id',
            'estado' => 'nullable|in:disponible,agotado,oculto',
            'stock_minimo' => 'nullable|boolean',
            'search' => 'nullable|string|max:100',
            'categoria' => 'nullable|string|max:100',
            'precio_min' => 'nullable|numeric|min:0',
            'precio_max' => 'nullable|numeric|min:0|gt:precio_min',
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
            $query = Producto::with('vendedor');

            // Filtros
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->boolean('stock_minimo')) {
                $query->where('stock', '<=', 5);
            }

            if ($request->filled('categoria')) {
                $query->where('categoria', $request->categoria);
            }

            if ($request->filled('precio_min')) {
                $query->where('precio', '>=', $request->precio_min);
            }

            if ($request->filled('precio_max')) {
                $query->where('precio', '<=', $request->precio_max);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 15);
            $productos = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Estadísticas
            $stats = [
                'total' => Producto::count(),
                'disponibles' => Producto::where('estado', 'disponible')->count(),
                'agotados' => Producto::where('estado', 'agotado')->count(),
                'ocultos' => Producto::where('estado', 'oculto')->count(),
                'stock_total' => Producto::sum('stock'),
                'valor_inventario' => Producto::selectRaw('sum(stock * precio) as total')->value('total') ?? 0,
                'stock_bajo' => Producto::where('stock', '<=', 5)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $productos,
                'estadisticas' => $stats,
                'message' => 'Productos obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener productos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de producto
     */
    public function show($id)
    {
        try {
            $producto = Producto::with('vendedor')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $producto,
                'message' => 'Producto obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }
    }

    /**
     * Crear nuevo producto
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'user_id' => 'required|exists:users,id',
            'estado' => 'required|in:disponible,agotado,oculto',
            'categoria' => 'nullable|string|max:100',
            'imagen_url' => 'nullable|image|max:2048',
            'destacado' => 'boolean',
            'especificaciones' => 'nullable|array',
            'peso' => 'nullable|numeric|min:0',
            'dimensiones' => 'nullable|string|max:100',
            'marca' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:50|unique:productos',
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

            // Procesar especificaciones
            if (isset($data['especificaciones'])) {
                $data['especificaciones'] = json_encode($data['especificaciones'], JSON_UNESCAPED_UNICODE);
            }

            // Generar SKU si no se proporciona
            if (!isset($data['sku'])) {
                $data['sku'] = 'PROD-' . strtoupper(uniqid());
            }

            // Subir imagen
            if ($request->hasFile('imagen_url')) {
                $path = $request->file('imagen_url')->store('productos', 'public');
                $data['imagen_url'] = $path;
            }

            $producto = Producto::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $producto->load('vendedor'),
                'message' => 'Producto creado exitosamente'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear producto: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar producto
     */
    public function update(Request $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'sometimes|required|numeric|min:0',
            'stock' => 'sometimes|required|integer|min:0',
            'user_id' => 'sometimes|required|exists:users,id',
            'estado' => 'sometimes|required|in:disponible,agotado,oculto',
            'categoria' => 'nullable|string|max:100',
            'imagen_url' => 'nullable|image|max:2048',
            'destacado' => 'boolean',
            'especificaciones' => 'nullable|array',
            'peso' => 'nullable|numeric|min:0',
            'dimensiones' => 'nullable|string|max:100',
            'marca' => 'nullable|string|max:100',
            'sku' => 'nullable|string|max:50|unique:productos,sku,' . $id,
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

            if (isset($data['especificaciones'])) {
                $data['especificaciones'] = json_encode($data['especificaciones'], JSON_UNESCAPED_UNICODE);
            }

            // Subir nueva imagen
            if ($request->hasFile('imagen_url')) {
                if ($producto->imagen_url) {
                    Storage::disk('public')->delete($producto->imagen_url);
                }
                $path = $request->file('imagen_url')->store('productos', 'public');
                $data['imagen_url'] = $path;
            }

            $producto->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $producto->fresh('vendedor'),
                'message' => 'Producto actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar producto: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar producto
     */
    public function destroy($id)
    {
        try {
            $producto = Producto::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        DB::beginTransaction();

        try {
            // Verificar si tiene pedidos asociados
            if ($producto->pedidos()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el producto porque tiene pedidos asociados'
                ], 422);
            }

            if ($producto->imagen_url) {
                Storage::disk('public')->delete($producto->imagen_url);
            }

            $producto->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar producto: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado del producto
     */
    public function cambiarEstado(Request $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:disponible,agotado,oculto'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $producto->update(['estado' => $request->estado]);

            return response()->json([
                'success' => true,
                'data' => $producto,
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
     * Actualizar stock
     */
    public function actualizarStock(Request $request, $id)
    {
        try {
            $producto = Producto::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'stock' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $producto->update(['stock' => $request->stock]);

            // Actualizar estado automáticamente si stock es 0
            if ($request->stock == 0 && $producto->estado == 'disponible') {
                $producto->update(['estado' => 'agotado']);
            } elseif ($request->stock > 0 && $producto->estado == 'agotado') {
                $producto->update(['estado' => 'disponible']);
            }

            return response()->json([
                'success' => true,
                'data' => $producto->fresh(),
                'message' => 'Stock actualizado correctamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar stock: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Productos con stock bajo
     */
    public function stockBajo()
    {
        try {
            $productos = Producto::with('vendedor')
                ->where('stock', '<=', 5)
                ->where('estado', '!=', 'oculto')
                ->orderBy('stock')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $productos,
                'message' => 'Productos con stock bajo obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener productos con stock bajo: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos con stock bajo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
