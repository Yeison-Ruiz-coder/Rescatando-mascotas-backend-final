<?php
// app/Http/Controllers/Api/V1/Public/SuscripcionPublicController.php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use App\Models\Mascota;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SuscripcionPublicController extends Controller
{
    use ApiResponses;

    /**
     * Ver todos los planes de apadrinamiento disponibles (PÚBLICO)
     */
    public function planes()
    {
        // Aquí puedes devolver los planes predefinidos o las mascotas disponibles para apadrinar
        $mascotasDisponibles = Mascota::with('fundacion')
            ->where('estado', 'En adopcion')
            ->where('destacada', true)
            ->orWhere('necesita_apadrinamiento', true)
            ->get(['id', 'nombre_mascota', 'foto_principal', 'descripcion', 'fundacion_id']);

        return $this->successResponse($mascotasDisponibles, 'Planes de apadrinamiento obtenidos');
    }

    /**
     * Ver detalle de un plan específico (PÚBLICO)
     */
    public function planDetalle(int $id)
    {
        $mascota = Mascota::with('fundacion')
            ->where('estado', 'En adopcion')
            ->findOrFail($id);

        return $this->successResponse($mascota, 'Plan de apadrinamiento obtenido');
    }

    /**
     * Ver mis suscripciones (USUARIO AUTENTICADO)
     */
    public function misSuscripciones(Request $request)
    {
        $suscripciones = Suscripcion::with(['mascota', 'mascota.fundacion'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->successResponse($suscripciones, 'Tus suscripciones obtenidas');
    }

    /**
     * Crear una nueva suscripción (USUARIO AUTENTICADO)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mascota_id' => 'required|exists:mascotas,id',
            'monto_mensual' => 'required|numeric|min:5000',
            'frecuencia' => 'required|in:unica,mensual,trimestral,anual',
            'mensaje_apoyo' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        $mascota = Mascota::findOrFail($request->mascota_id);

        // Verificar que la mascota esté disponible para apadrinar
        if ($mascota->estado !== 'En adopcion') {
            return $this->errorResponse('Esta mascota no está disponible para apadrinamiento', null, 400);
        }

        $suscripcion = Suscripcion::create([
            'user_id' => $request->user()->id,
            'mascota_id' => $request->mascota_id,
            'monto_mensual' => $request->monto_mensual,
            'frecuencia' => $request->frecuencia,
            'mensaje_apoyo' => $request->mensaje_apoyo,
            'fecha_inicio' => now(),
            'estado' => 'activo',
        ]);

        return $this->successResponse($suscripcion->load(['mascota', 'mascota.fundacion']), 'Suscripción creada exitosamente', 201);
    }

    /**
     * Ver detalle de una suscripción (USUARIO AUTENTICADO)
     */
    public function show(Request $request,int $id)
    {
        $suscripcion = Suscripcion::with(['mascota', 'mascota.fundacion'])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return $this->successResponse($suscripcion, 'Suscripción obtenida');
    }

    /**
     * Cancelar una suscripción (USUARIO AUTENTICADO)
     */
    public function cancelar(Request $request,int $id)
    {
        $suscripcion = Suscripcion::where('user_id', $request->user()->id)
            ->whereIn('estado', ['activo', 'pausado'])
            ->findOrFail($id);

        $suscripcion->update([
            'estado' => 'cancelado',
            'fecha_fin' => now(),
        ]);

        return $this->successResponse($suscripcion, 'Suscripción cancelada exitosamente');
    }

    /**
     * Pausar una suscripción (USUARIO AUTENTICADO)
     */
    public function pausar(Request $request,int $id)
    {
        $suscripcion = Suscripcion::where('user_id', $request->user()->id)
            ->where('estado', 'activo')
            ->findOrFail($id);

        $suscripcion->update(['estado' => 'pausado']);

        return $this->successResponse($suscripcion, 'Suscripción pausada exitosamente');
    }

    /**
     * Reactivar una suscripción (USUARIO AUTENTICADO)
     */
    public function reactivar(Request $request,int $id)
    {
        $suscripcion = Suscripcion::where('user_id', $request->user()->id)
            ->where('estado', 'pausado')
            ->findOrFail($id);

        $suscripcion->update(['estado' => 'activo']);

        return $this->successResponse($suscripcion, 'Suscripción reactivada exitosamente');
    }
}
