<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PedidoController extends Controller
{
    /**
     * Listado de pedidos del usuario
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Pedido::with(['vendedor', 'productos'])
            ->where('comprador_id', $user->id);

        // Filtro por estado
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $pedidos = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $pedidos
        ]);
    }

    /**
     * Detalle de pedido
     */
    public function show($id)
    {
        $user = request()->user();

        $pedido = Pedido::with(['vendedor', 'productos'])
            ->where('comprador_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $pedido
        ]);
    }

    /**
     * Procesar checkout (crear pedido desde carrito)
     */
    public function checkout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'direccion_envio' => 'required|string|max:255',
            'telefono_contacto' => 'required|string|max:20',
            'notas' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $carrito = session()->get('carrito_' . $request->user()->id, []);

        if (empty($carrito)) {
            return response()->json([
                'success' => false,
                'message' => 'El carrito está vacío'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Agrupar items por vendedor
            $itemsPorVendedor = [];
            foreach ($carrito as $item) {
                $vendedorId = $item['vendedor_id'];
                if (!isset($itemsPorVendedor[$vendedorId])) {
                    $itemsPorVendedor[$vendedorId] = [];
                }
                $itemsPorVendedor[$vendedorId][] = $item;
            }

            $pedidosCreados = [];

            foreach ($itemsPorVendedor as $vendedorId => $items) {
                $subtotal = 0;
                foreach ($items as $item) {
                    $subtotal += $item['precio'] * $item['cantidad'];
                }

                $pedido = Pedido::create([
                    'codigo' => 'PED-' . strtoupper(uniqid()),
                    'comprador_id' => $request->user()->id,
                    'vendedor_id' => $vendedorId,
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                    'estado' => 'pendiente',
                    'direccion_envio' => $request->direccion_envio,
                    'telefono_contacto' => $request->telefono_contacto,
                    'notas' => $request->notas,
                ]);

                // Agregar productos a la tabla pivote
                foreach ($items as $item) {
                    $producto = Producto::find($item['id']);

                    if ($producto->stock < $item['cantidad']) {
                        throw new \Exception("Stock insuficiente para {$producto->nombre}");
                    }

                    $pedido->productos()->attach($item['id'], [
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio'],
                        'subtotal' => $item['precio'] * $item['cantidad'],
                    ]);

                    // Actualizar stock
                    $producto->stock -= $item['cantidad'];

                    if ($producto->stock == 0) {
                        $producto->estado = 'agotado';
                    }

                    $producto->save();
                }

                $pedidosCreados[] = $pedido->id;
            }

            // Limpiar carrito
            session()->forget('carrito_' . $request->user()->id);

            DB::commit();

            $primerPedido = Pedido::with('productos')->find($pedidosCreados[0]);

            return response()->json([
                'success' => true,
                'message' => count($pedidosCreados) > 1
                    ? 'Tus pedidos han sido creados exitosamente'
                    : 'Pedido creado exitosamente',
                'data' => [
                    'pedidos' => $pedidosCreados,
                    'primer_pedido' => $primerPedido
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar pedido (solo si está pendiente)
     */
    public function cancelar(Request $request, $id)
    {
        $user = $request->user();

        $pedido = Pedido::with('productos')
            ->where('comprador_id', $user->id)
            ->where('estado', 'pendiente')
            ->findOrFail($id);

        DB::transaction(function () use ($pedido) {
            // Restaurar stock
            foreach ($pedido->productos as $producto) {
                $producto->stock += $producto->pivot->cantidad;
                $producto->estado = 'disponible';
                $producto->save();
            }

            $pedido->estado = 'cancelado';
            $pedido->save();
        });

        return response()->json([
            'success' => true,
            'message' => 'Pedido cancelado exitosamente'
        ]);
    }

    /**
     * Confirmar recepción de pedido
     */
    public function confirmarRecepcion($id)
    {
        $user = request()->user();

        $pedido = Pedido::where('comprador_id', $user->id)
            ->where('estado', 'enviado')
            ->findOrFail($id);

        $pedido->update(['estado' => 'entregado']);

        return response()->json([
            'success' => true,
            'message' => 'Recepción confirmada. ¡Gracias por tu compra!'
        ]);
    }
}
