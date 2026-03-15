<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Listado de productos disponibles
     */
    public function index(Request $request)
    {
        $query = Producto::with('vendedor')
            ->where('estado', 'disponible')
            ->where('stock', '>', 0);

        if ($request->has('categoria')) {
            $query->where('categoria_id', $request->categoria);
        }

        if ($request->has('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        // Ordenamiento
        switch ($request->get('orden', 'reciente')) {
            case 'precio_asc':
                $query->orderBy('precio', 'asc');
                break;
            case 'precio_desc':
                $query->orderBy('precio', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $productos = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $productos
        ]);
    }

    /**
     * Productos por categoría
     */
    public function porCategoria($categoriaId)
    {
        $categoria = Categoria::findOrFail($categoriaId);

        $productos = Producto::with('vendedor')
            ->where('categoria_id', $categoriaId)
            ->where('estado', 'disponible')
            ->where('stock', '>', 0)
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => [
                'categoria' => $categoria,
                'productos' => $productos
            ]
        ]);
    }

    /**
     * Detalle de producto
     */
    public function show($id)
    {
        $producto = Producto::with('vendedor')
            ->where('estado', 'disponible')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $producto
        ]);
    }
}
