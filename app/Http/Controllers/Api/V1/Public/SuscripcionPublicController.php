<?php

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
        $mascotasDisponibles = Mascota::query()
            ->selectFields()
            ->with(['fundacion:id,Nombre_1,imagen_portada,ciudad'])
            ->where('estado', 'En adopcion')
            ->where(function ($query) {
                $query->where('destacada', true)
                    ->orWhere('necesita_apadrinamiento', true);
            })
            ->get();

        return $this->successResponse($mascotasDisponibles, 'Planes de apadrinamiento obtenidos');
    }

    /**
     * Ver detalle de un plan específico (PÚBLICO)
     */
    public function planDetalle(int $id)
    {
        $mascota = Mascota::query()
            ->selectFields()
            ->with(['fundacion:id,Nombre_1,imagen_portada,ciudad'])
            ->where('estado', 'En adopcion')
            ->findOrFail($id);

        return $this->successResponse($mascota, 'Plan de apadrinamiento obtenido');
    }

    /**
     * ✅ Crear una nueva suscripción (PÚBLICA - sin autenticación)
     * POST /api/v1/public/suscripciones-crear
     */
    public function storePublic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:suscripciones,email',
            'nombre' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'mascota_id' => 'nullable|exists:mascotas,id',
            'monto_mensual' => 'nullable|numeric|min:5000',
            'frecuencia' => 'nullable|in:unica,mensual,trimestral,anual',
            'mensaje_apoyo' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            // Si se envía mascota_id, verificar que exista
            if ($request->mascota_id) {
                $mascota = Mascota::find($request->mascota_id);
                if (!$mascota) {
                    return $this->errorResponse('La mascota seleccionada no existe', null, 404);
                }

                // Verificar que la mascota esté disponible para apadrinar
                if ($mascota->estado !== 'En adopcion') {
                    return $this->errorResponse('Esta mascota no está disponible para apadrinamiento', null, 400);
                }
            }

            $suscripcion = Suscripcion::create([
                'email' => $request->email,
                'nombre' => $request->nombre,
                'telefono' => $request->telefono,
                'mascota_id' => $request->mascota_id,
                'monto_mensual' => $request->monto_mensual ?? 10000,
                'frecuencia' => $request->frecuencia ?? 'mensual',
                'mensaje_apoyo' => $request->mensaje_apoyo,
                'estado' => 'activo',
                'fecha_inicio' => now(),
                'user_id' => null, // Sin usuario autenticado
            ]);

            return $this->successResponse(
                $suscripcion,
                '¡Suscripción creada exitosamente! Gracias por apoyar a las mascotas 🐾',
                201
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al crear la suscripción',
                $e->getMessage(),
                500
            );
        }
    }

    /**
     * Ver mis suscripciones (USUARIO AUTENTICADO)
     */
    public function misSuscripciones(Request $request)
    {
        $suscripciones = Suscripcion::query()
            ->selectFields()
            ->with([
                'mascota' => function ($query) {
                    $query->selectFields()->with('fundacion:id,Nombre_1,imagen_portada,ciudad');
                },
                'user:id,nombre,email',
            ])
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

        $mascota = Mascota::query()
            ->select(['id', 'estado'])
            ->findOrFail($request->mascota_id);

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

        return $this->successResponse(
            $suscripcion->load([
                'mascota' => function ($query) {
                    $query->selectFields()->with('fundacion:id,Nombre_1,imagen_portada,ciudad');
                },
                'user:id,nombre,email',
            ]),
            'Suscripción creada exitosamente',
            201
        );
    }

    /**
     * Ver detalle de una suscripción (USUARIO AUTENTICADO)
     */
    public function show(Request $request, int $id)
    {
        $suscripcion = Suscripcion::query()
            ->selectFields()
            ->with([
                'mascota' => function ($query) {
                    $query->selectFields()->with('fundacion:id,Nombre_1,imagen_portada,ciudad');
                },
                'user:id,nombre,email',
            ])
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return $this->successResponse($suscripcion, 'Suscripción obtenida');
    }

    /**
     * Cancelar una suscripción (USUARIO AUTENTICADO)
     */
    public function cancelar(Request $request, int $id)
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
    public function pausar(Request $request, int $id)
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
    public function reactivar(Request $request, int $id)
    {
        $suscripcion = Suscripcion::where('user_id', $request->user()->id)
            ->where('estado', 'pausado')
            ->findOrFail($id);

        $suscripcion->update(['estado' => 'activo']);

        return $this->successResponse($suscripcion, 'Suscripción reactivada exitosamente');
    }
}
