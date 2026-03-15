<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    /**
     * Listado de pedidos
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'estado' => 'nullable|in:pendiente,pagado,enviado,entregado,cancelado',
            'comprador_id' => 'nullable|exists:users,id',
            'vendedor_id' => 'nullable|exists:users,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
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
            $query = Pedido::with(['comprador', 'vendedor']);

            // Filtros
            if ($request->filled('estado')) {
                $query->where('estado', $request->estado);
            }

            if ($request->filled('comprador_id')) {
                $query->where('comprador_id', $request->comprador_id);
            }

            if ($request->filled('vendedor_id')) {
                $query->where('vendedor_id', $request->vendedor_id);
            }

            if ($request->filled('fecha_inicio')) {
                $query->whereDate('created_at', '>=', $request->fecha_inicio);
            }

            if ($request->filled('fecha_fin')) {
                $query->whereDate('created_at', '<=', $request->fecha_fin);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('codigo', 'like', "%{$search}%")
                      ->orWhere('direccion_envio', 'like', "%{$search}%")
                      ->orWhere('telefono_contacto', 'like', "%{$search}%");
                });
            }

            $perPage = $request->get('per_page', 15);
            $pedidos = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Estadísticas
            $stats = [
                'total' => Pedido::count(),
                'pendientes' => Pedido::where('estado', 'pendiente')->count(),
                'pagados' => Pedido::where('estado', 'pagado')->count(),
                'enviados' => Pedido::where('estado', 'enviado')->count(),
                'entregados' => Pedido::where('estado', 'entregado')->count(),
                'cancelados' => Pedido::where('estado', 'cancelado')->count(),
                'ingresos_totales' => Pedido::whereIn('estado', ['pagado', 'enviado', 'entregado'])->sum('total'),
                'ingresos_mes' => Pedido::whereIn('estado', ['pagado', 'enviado', 'entregado'])
                    ->whereMonth('created_at', now()->month)
                    ->sum('total'),
            ];

            return response()->json([
                'success' => true,
                'data' => $pedidos,
                'estadisticas' => $stats,
                'message' => 'Pedidos obtenidos exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al obtener pedidos: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los pedidos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar detalle de pedido
     */
    public function show($id)
    {
        try {
            $pedido = Pedido::with(['comprador', 'vendedor'])->findOrFail($id);


            return response()->json([
                'success' => true,
                'data' => [
                    'pedido' => $pedido,
                ],
                'message' => 'Pedido obtenido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado'
            ], 404);
        }
    }

    /**
     * Actualizar pedido
     */
    public function update(Request $request, $id)
    {
        try {
            $pedido = Pedido::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'sometimes|required|in:pendiente,pagado,enviado,entregado,cancelado',
            'direccion_envio' => 'sometimes|required|string|max:255',
            'telefono_contacto' => 'sometimes|required|string|max:20',
            'notas' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pedido->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $pedido->fresh(['comprador', 'vendedor']),
                'message' => 'Pedido actualizado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al actualizar pedido: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar pedido
     */
    public function destroy($id)
    {
        try {
            $pedido = Pedido::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado'
            ], 404);
        }

        try {
            // Solo permitir eliminar pedidos pendientes o cancelados
            if (!in_array($pedido->estado, ['pendiente', 'cancelado'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden eliminar pedidos pendientes o cancelados'
                ], 422);
            }

            $pedido->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pedido eliminado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al eliminar pedido: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el pedido',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado del pedido
     */
    public function cambiarEstado(Request $request, $id)
    {
        try {
            $pedido = Pedido::findOrFail($id);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pedido no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'estado' => 'required|in:pendiente,pagado,enviado,entregado,cancelado'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pedido->update(['estado' => $request->estado]);

            return response()->json([
                'success' => true,
                'data' => $pedido,
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
     * Reporte de ventas
     */
    public function reporte(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'vendedor_id' => 'nullable|exists:users,id',
            'agrupacion' => 'nullable|in:dia,mes,vendedor',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $query = Pedido::whereBetween('created_at', [$request->fecha_inicio, $request->fecha_fin])
                ->whereIn('estado', ['pagado', 'enviado', 'entregado']);

            if ($request->filled('vendedor_id')) {
                $query->where('vendedor_id', $request->vendedor_id);
            }

            $pedidos = $query->with(['comprador', 'vendedor'])->get();

            $totalVentas = $pedidos->sum('total');
            $promedioVenta = $pedidos->avg('total');
            $cantidadPedidos = $pedidos->count();
            $ventaMaxima = $pedidos->max('total');
            $ventaMinima = $pedidos->min('total');

            $resultado = [
                'periodo' => [
                    'inicio' => $request->fecha_inicio,
                    'fin' => $request->fecha_fin,
                ],
                'estadisticas' => [
                    'total_ventas' => $totalVentas,
                    'promedio_venta' => round($promedioVenta ?? 0, 2),
                    'cantidad_pedidos' => $cantidadPedidos,
                    'venta_maxima' => $ventaMaxima,
                    'venta_minima' => $ventaMinima,
                ],
                'pedidos' => $pedidos,
            ];

            // Agrupar según lo solicitado
            $agrupacion = $request->get('agrupacion', 'dia');

            if ($agrupacion === 'dia') {
                $resultado['agrupado'] = $pedidos->groupBy(function($item) {
                    return $item->created_at->format('Y-m-d');
                })->map(function($grupo) {
                    return [
                        'cantidad' => $grupo->count(),
                        'total' => $grupo->sum('total')
                    ];
                });
            } elseif ($agrupacion === 'mes') {
                $resultado['agrupado'] = $pedidos->groupBy(function($item) {
                    return $item->created_at->format('Y-m');
                })->map(function($grupo) {
                    return [
                        'cantidad' => $grupo->count(),
                        'total' => $grupo->sum('total')
                    ];
                });
            } elseif ($agrupacion === 'vendedor') {
                $resultado['agrupado'] = $pedidos->groupBy(function($item) {
                    return $item->vendedor->nombre ?? 'Sin Vendedor';
                })->map(function($grupo) {
                    return [
                        'cantidad' => $grupo->count(),
                        'total' => $grupo->sum('total')
                    ];
                });
            }

            return response()->json([
                'success' => true,
                'data' => $resultado,
                'message' => 'Reporte generado exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al generar reporte: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
