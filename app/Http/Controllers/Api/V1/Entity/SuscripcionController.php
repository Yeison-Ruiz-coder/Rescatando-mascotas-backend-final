<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SuscripcionController extends Controller
{
    /**
     * Display a listing of the resource (solo suscripciones de las mascotas de la fundación)
     */
    public function index()
    {
        // Obtener las mascotas de la fundación autenticada
        $mascotasIds = Mascota::where('fundacion_id', auth()->user()->fundacion_id)
            ->pluck('id');
        
        // Obtener suscripciones de esas mascotas
        $suscripciones = Suscripcion::with(['user', 'mascota'])
            ->whereIn('mascota_id', $mascotasIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $suscripciones
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'mascota_id' => 'required|exists:mascotas,id',
            'monto_mensual' => 'required|numeric|min:1',
            'frecuencia' => 'required|in:unica,mensual,trimestral,anual',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'mensaje_apoyo' => 'nullable|string',
            'estado' => 'required|in:activo,pausado,cancelado,finalizado'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Verificar que la mascota pertenece a la fundación
            $mascota = Mascota::where('id', $request->mascota_id)
                ->where('fundacion_id', auth()->user()->fundacion_id)
                ->first();

            if (!$mascota) {
                return response()->json([
                    'success' => false,
                    'message' => 'La mascota no pertenece a tu fundación'
                ], 403);
            }

            $suscripcion = new Suscripcion();
            $suscripcion->user_id = $request->user_id;
            $suscripcion->mascota_id = $request->mascota_id;
            $suscripcion->monto_mensual = $request->monto_mensual;
            $suscripcion->frecuencia = $request->frecuencia;
            $suscripcion->fecha_inicio = $request->fecha_inicio;
            $suscripcion->fecha_fin = $request->fecha_fin;
            $suscripcion->mensaje_apoyo = $request->mensaje_apoyo;
            $suscripcion->estado = $request->estado;
            
            $suscripcion->save();

            return response()->json([
                'success' => true,
                'message' => 'Suscripción creada exitosamente',
                'data' => $suscripcion->load(['user', 'mascota'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la suscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource (solo si la mascota pertenece a la fundación)
     */
    public function show($id)
    {
        try {
            // Obtener mascotas de la fundación
            $mascotasIds = Mascota::where('fundacion_id', auth()->user()->fundacion_id)
                ->pluck('id');

            $suscripcion = Suscripcion::with(['user', 'mascota'])
                ->whereIn('mascota_id', $mascotasIds)
                ->where('id', $id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $suscripcion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Suscripción no encontrada'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage (solo si la mascota pertenece a la fundación)
     */
    public function update(Request $request, $id)
    {
        try {
            // Verificar que la suscripción pertenece a una mascota de la fundación
            $mascotasIds = Mascota::where('fundacion_id', auth()->user()->fundacion_id)
                ->pluck('id');

            $suscripcion = Suscripcion::whereIn('mascota_id', $mascotasIds)
                ->where('id', $id)
                ->firstOrFail();

            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|exists:users,id',
                'mascota_id' => 'sometimes|exists:mascotas,id',
                'monto_mensual' => 'sometimes|numeric|min:1',
                'frecuencia' => 'sometimes|in:unica,mensual,trimestral,anual',
                'fecha_inicio' => 'sometimes|date',
                'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
                'mensaje_apoyo' => 'nullable|string',
                'estado' => 'sometimes|in:activo,pausado,cancelado,finalizado'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Si cambia la mascota, verificar que la nueva pertenece a la fundación
            if ($request->has('mascota_id') && $request->mascota_id != $suscripcion->mascota_id) {
                $nuevaMascota = Mascota::where('id', $request->mascota_id)
                    ->where('fundacion_id', auth()->user()->fundacion_id)
                    ->first();

                if (!$nuevaMascota) {
                    return response()->json([
                        'success' => false,
                        'message' => 'La mascota no pertenece a tu fundación'
                    ], 403);
                }
            }

            // Actualizar solo los campos que vienen en la request
            if ($request->has('user_id')) $suscripcion->user_id = $request->user_id;
            if ($request->has('mascota_id')) $suscripcion->mascota_id = $request->mascota_id;
            if ($request->has('monto_mensual')) $suscripcion->monto_mensual = $request->monto_mensual;
            if ($request->has('frecuencia')) $suscripcion->frecuencia = $request->frecuencia;
            if ($request->has('fecha_inicio')) $suscripcion->fecha_inicio = $request->fecha_inicio;
            if ($request->has('fecha_fin')) $suscripcion->fecha_fin = $request->fecha_fin;
            if ($request->has('mensaje_apoyo')) $suscripcion->mensaje_apoyo = $request->mensaje_apoyo;
            if ($request->has('estado')) $suscripcion->estado = $request->estado;

            $suscripcion->save();

            return response()->json([
                'success' => true,
                'message' => 'Suscripción actualizada exitosamente',
                'data' => $suscripcion->load(['user', 'mascota'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la suscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (solo si la mascota pertenece a la fundación)
     */
    public function destroy($id)
    {
        try {
            $mascotasIds = Mascota::where('fundacion_id', auth()->user()->fundacion_id)
                ->pluck('id');

            $suscripcion = Suscripcion::whereIn('mascota_id', $mascotasIds)
                ->where('id', $id)
                ->firstOrFail();
                
            $suscripcion->delete();

            return response()->json([
                'success' => true,
                'message' => 'Suscripción eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la suscripción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get suscripciones by mascota (solo si la mascota pertenece a la fundación)
     */
    public function porMascota($mascotaId)
    {
        // Verificar que la mascota pertenece a la fundación
        $mascota = Mascota::where('id', $mascotaId)
            ->where('fundacion_id', auth()->user()->fundacion_id)
            ->first();

        if (!$mascota) {
            return response()->json([
                'success' => false,
                'message' => 'Mascota no encontrada o no pertenece a tu fundación'
            ], 404);
        }

        $suscripciones = Suscripcion::with(['user'])
            ->where('mascota_id', $mascotaId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $suscripciones
        ]);
    }

    /**
     * Get estadísticas de suscripciones de la fundación
     */
    public function estadisticas()
    {
        $mascotasIds = Mascota::where('fundacion_id', auth()->user()->fundacion_id)
            ->pluck('id');

        $totalSuscripciones = Suscripcion::whereIn('mascota_id', $mascotasIds)->count();
        $activas = Suscripcion::whereIn('mascota_id', $mascotasIds)
            ->where('estado', 'activo')
            ->count();
        $pausadas = Suscripcion::whereIn('mascota_id', $mascotasIds)
            ->where('estado', 'pausado')
            ->count();
        $canceladas = Suscripcion::whereIn('mascota_id', $mascotasIds)
            ->where('estado', 'cancelado')
            ->count();
        $finalizadas = Suscripcion::whereIn('mascota_id', $mascotasIds)
            ->where('estado', 'finalizado')
            ->count();

        $totalMensual = Suscripcion::whereIn('mascota_id', $mascotasIds)
            ->where('estado', 'activo')
            ->sum('monto_mensual');

        return response()->json([
            'success' => true,
            'data' => [
                'total_suscripciones' => $totalSuscripciones,
                'activas' => $activas,
                'pausadas' => $pausadas,
                'canceladas' => $canceladas,
                'finalizadas' => $finalizadas,
                'ingreso_mensual_total' => $totalMensual
            ]
        ]);
    }
}