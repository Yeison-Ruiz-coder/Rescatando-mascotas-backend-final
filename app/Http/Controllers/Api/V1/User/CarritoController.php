<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CarritoController extends Controller
{
    /**
     * Mostrar carrito del usuario (guardado en sesión)
     */
    public function index(Request $request)
    {
        $carrito = session()->get('carrito_' . $request->user()->id, []);

        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'items' => array_values($carrito),
                'total' => $total,
                'cantidad_items' => count($carrito)
            ]
        ]);
    }

    /**
     * Agregar producto al carrito
     */
    public function agregar(Request $request, $productoId)
    {
        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $producto = Producto::where('estado', 'disponible')
            ->where('stock', '>=', $request->cantidad)
            ->findOrFail($productoId);

        $carrito = session()->get('carrito_' . $request->user()->id, []);

        if (isset($carrito[$productoId])) {
            $nuevaCantidad = $carrito[$productoId]['cantidad'] + $request->cantidad;

            if ($nuevaCantidad > $producto->stock) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stock insuficiente. Disponible: ' . $producto->stock
                ], 400);
            }

            $carrito[$productoId]['cantidad'] = $nuevaCantidad;
            $mensaje = 'Cantidad actualizada en el carrito';
        } else {
            $carrito[$productoId] = [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio' => $producto->precio,
                'cantidad' => $request->cantidad,
                'imagen' => $producto->imagen_url,
                'vendedor_id' => $producto->user_id,
                'stock_disponible' => $producto->stock
            ];
            $mensaje = 'Producto agregado al carrito';
        }

        session()->put('carrito_' . $request->user()->id, $carrito);

        return response()->json([
            'success' => true,
            'message' => $mensaje,
            'data' => [
                'items' => array_values($carrito),
                'total' => $this->calcularTotal($carrito)
            ]
        ]);
    }

    /**
     * Actualizar cantidad de un producto
     */
    public function actualizar(Request $request, $productoId)
    {
        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $producto = Producto::findOrFail($productoId);
        $carrito = session()->get('carrito_' . $request->user()->id, []);

        if (!isset($carrito[$productoId])) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado en el carrito'
            ], 404);
        }

        if ($request->cantidad > $producto->stock) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuficiente. Disponible: ' . $producto->stock
            ], 400);
        }

        $carrito[$productoId]['cantidad'] = $request->cantidad;
        session()->put('carrito_' . $request->user()->id, $carrito);

        return response()->json([
            'success' => true,
            'message' => 'Carrito actualizado',
            'data' => [
                'items' => array_values($carrito),
                'total' => $this->calcularTotal($carrito)
            ]
        ]);
    }

    /**
     * Eliminar producto del carrito
     */
    public function eliminar(Request $request, $productoId)
    {
        $carrito = session()->get('carrito_' . $request->user()->id, []);

        if (!isset($carrito[$productoId])) {
            return response()->json([
                'success' => false,
                'message' => 'Producto no encontrado en el carrito'
            ], 404);
        }

        unset($carrito[$productoId]);
        session()->put('carrito_' . $request->user()->id, $carrito);

        return response()->json([
            'success' => true,
            'message' => 'Producto eliminado del carrito',
            'data' => [
                'items' => array_values($carrito),
                'total' => $this->calcularTotal($carrito)
            ]
        ]);
    }

    /**
     * Vaciar carrito
     */
    public function vaciar(Request $request)
    {
        session()->forget('carrito_' . $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Carrito vaciado exitosamente'
        ]);
    }

    /**
     * Calcular total del carrito
     */
    private function calcularTotal($carrito)
    {
        $total = 0;
        foreach ($carrito as $item) {
            $total += $item['precio'] * $item['cantidad'];
        }
        return $total;
    }
}
