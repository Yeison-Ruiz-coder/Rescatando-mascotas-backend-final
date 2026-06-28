<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use App\Models\Mascota;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\log;
use Illuminate\Support\Facades\Validator;

class SuscripcionPublicController extends Controller
{
    use ApiResponses;

    /**
     * Ver todas las mascotas disponibles para apadrinar
     * GET /api/suscripciones/planes
     */
    public function planes()
    {
        try {
            $mascotas = Mascota::with(['fundacion' => function($q) {
                $q->select('id', 'Nombre_1', 'imagen_portada', 'ciudad');
            }])
            ->where('estado', 'En adopcion')
            ->where(function($query) {
                $query->where('destacada', true)
                      ->orWhere('necesita_apadrinamiento', true);
            })
            ->select('id', 'nombre', 'especie', 'raza', 'edad', 'descripcion', 'fundacion_id', 'imagen')
            ->get();

            $planes = $mascotas->map(function($mascota) {
                return [
                    'id' => $mascota->id,
                    'nombre' => $mascota->nombre,
                    'especie' => $mascota->especie,
                    'raza' => $mascota->raza,
                    'edad' => $mascota->edad,
                    'descripcion' => $mascota->descripcion,
                    'imagen' => $mascota->imagen,
                    'monto_recomendado' => 10000,
                    'fundacion' => $mascota->fundacion ? [
                        'id' => $mascota->fundacion->id,
                        'nombre' => $mascota->fundacion->Nombre_1,
                        'imagen' => $mascota->fundacion->imagen_portada,
                        'ciudad' => $mascota->fundacion->ciudad,
                    ] : null,
                ];
            });

            return $this->successResponse($planes, 'Planes de apadrinamiento obtenidos');
        } catch (\Exception $e) {
            Log::error('Error en planes:', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error al obtener planes', $e->getMessage(), 500);
        }
    }

    /**
     * Ver detalle de un plan específico
     * GET /api/suscripciones/planes/{id}
     */
    public function planDetalle(int $id)
    {
        try {
            $mascota = Mascota::with(['fundacion' => function($q) {
                $q->select('id', 'Nombre_1', 'imagen_portada', 'ciudad', 'descripcion');
            }])
            ->where('estado', 'En adopcion')
            ->findOrFail($id);

            $plan = [
                'id' => $mascota->id,
                'nombre' => $mascota->nombre,
                'especie' => $mascota->especie,
                'raza' => $mascota->raza,
                'edad' => $mascota->edad,
                'descripcion' => $mascota->descripcion,
                'imagen' => $mascota->imagen,
                'monto_recomendado' => 10000,
                'fundacion' => $mascota->fundacion ? [
                    'id' => $mascota->fundacion->id,
                    'nombre' => $mascota->fundacion->Nombre_1,
                    'imagen' => $mascota->fundacion->imagen_portada,
                    'ciudad' => $mascota->fundacion->ciudad,
                    'descripcion' => $mascota->fundacion->descripcion,
                ] : null,
            ];

            return $this->successResponse($plan, 'Plan obtenido');
        } catch (\Exception $e) {
            return $this->errorResponse('Plan no encontrado', null, 404);
        }
    }

    /**
     * ✅ CREAR SUSCRIPCIÓN (Usuario autenticado)
     * POST /api/suscripciones/user/crear
     *
     * 🔥 MODIFICACIÓN: La suscripción se crea como PENDIENTE
     * y se activa después del pago
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

        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Debes iniciar sesión para apadrinar', null, 401);
            }

            $mascota = Mascota::find($request->mascota_id);
            if (!$mascota || $mascota->estado !== 'En adopcion') {
                return $this->errorResponse('Esta mascota no está disponible para apadrinamiento', null, 400);
            }

            // Verificar si ya tiene una suscripción activa para esta mascota
            $existe = Suscripcion::where('user_id', $user->id)
                ->where('mascota_id', $request->mascota_id)
                ->whereIn('estado', ['activo', 'pausado', 'pendiente'])
                ->exists();

            if ($existe) {
                return $this->errorResponse('Ya tienes una suscripción activa o pendiente para esta mascota', null, 400);
            }

            // ✅ CREAR SUSCRIPCIÓN EN ESTADO PENDIENTE
            // El usuario debe pagar para activarla
            $suscripcion = Suscripcion::create([
                'user_id' => $user->id,
                'mascota_id' => $request->mascota_id,
                'monto_mensual' => $request->monto_mensual,
                'frecuencia' => $request->frecuencia,
                'mensaje_apoyo' => $request->mensaje_apoyo,
                'fecha_inicio' => null, // Se activa después del pago
                'estado' => 'pendiente', // ⚠️ CAMBIADO: pendiente en lugar de activo
                'es_demo' => true, // Por defecto demo
                'payment_method' => null,
                'payment_reference' => null,
            ]);

            // Retornar la suscripción para que el frontend inicie el pago
            return $this->successResponse([
                'suscripcion' => $suscripcion->load(['mascota', 'user']),
                'mensaje' => 'Suscripción creada. Por favor, completa el pago para activarla.',
                'next_step' => 'payment',
            ], 'Suscripción creada - Pendiente de pago', 201);

        } catch (\Exception $e) {
            Log::error('Error al crear suscripción:', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error al crear la suscripción', $e->getMessage(), 500);
        }
    }

    /**
     * Ver mis suscripciones (USUARIO AUTENTICADO)
     * GET /api/suscripciones/user/mis-suscripciones
     */
    public function misSuscripciones(Request $request)
    {
        try {
            $suscripciones = Suscripcion::with([
                'mascota' => function($q) {
                    $q->select('id', 'nombre', 'especie', 'raza', 'edad', 'imagen', 'fundacion_id')
                      ->with('fundacion:id,Nombre_1,imagen_portada,ciudad');
                },
                'pagos' => function($q) {
                    $q->orderBy('created_at', 'desc')->limit(1);
                }
            ])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

            return $this->successResponse($suscripciones, 'Tus suscripciones obtenidas');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al obtener suscripciones', $e->getMessage(), 500);
        }
    }

    /**
     * Ver detalle de una suscripción (USUARIO AUTENTICADO)
     * GET /api/suscripciones/user/{id}
     */
    public function show(Request $request, int $id)
    {
        try {
            $suscripcion = Suscripcion::with(['mascota', 'mascota.fundacion', 'pagos'])
                ->where('user_id', $request->user()->id)
                ->findOrFail($id);

            return $this->successResponse($suscripcion, 'Suscripción obtenida');
        } catch (\Exception $e) {
            return $this->errorResponse('Suscripción no encontrada', null, 404);
        }
    }

    /**
     * Cancelar una suscripción (USUARIO AUTENTICADO)
     * PATCH /api/suscripciones/user/{id}/cancelar
     */
    public function cancelar(Request $request, int $id)
    {
        try {
            $suscripcion = Suscripcion::where('user_id', $request->user()->id)
                ->whereIn('estado', ['activo', 'pausado', 'pendiente'])
                ->findOrFail($id);

            $suscripcion->update([
                'estado' => 'cancelado',
                'fecha_fin' => now(),
            ]);

            return $this->successResponse($suscripcion, 'Suscripción cancelada exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al cancelar la suscripción', $e->getMessage(), 500);
        }
    }

    /**
     * Pausar una suscripción (USUARIO AUTENTICADO)
     * PATCH /api/suscripciones/user/{id}/pausar
     */
    public function pausar(Request $request, int $id)
    {
        try {
            $suscripcion = Suscripcion::where('user_id', $request->user()->id)
                ->where('estado', 'activo')
                ->findOrFail($id);

            $suscripcion->update(['estado' => 'pausado']);

            return $this->successResponse($suscripcion, 'Suscripción pausada exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al pausar la suscripción', $e->getMessage(), 500);
        }
    }

    /**
     * Reactivar una suscripción (USUARIO AUTENTICADO)
     * PATCH /api/suscripciones/user/{id}/reactivar
     */
    public function reactivar(Request $request, int $id)
    {
        try {
            $suscripcion = Suscripcion::where('user_id', $request->user()->id)
                ->where('estado', 'pausado')
                ->findOrFail($id);

            $suscripcion->update(['estado' => 'activo']);

            return $this->successResponse($suscripcion, 'Suscripción reactivada exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al reactivar la suscripción', $e->getMessage(), 500);
        }
    }
}
