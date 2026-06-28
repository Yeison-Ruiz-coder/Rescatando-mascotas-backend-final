<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use App\Models\Mascota;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            // ✅ Obtener todas las mascotas en adopción SIN filtros complejos
            $mascotas = Mascota::where('estado', 'En adopcion')
                ->limit(20)
                ->get();

            $planes = $mascotas->map(function ($mascota) {
                return [
                    'id' => $mascota->id,
                    'nombre' => $mascota->nombre_mascota ?? 'Sin nombre',
                    'especie' => $mascota->especie ?? 'No especificada',
                    'raza' => $mascota->raza ?? 'No especificada',
                    'edad' => $mascota->edad_aprox ?? 0,
                    'descripcion' => $mascota->descripcion ?? '',
                    'imagen' => $mascota->foto_principal ?? null,
                    'monto_recomendado' => 10000,
                    'fundacion' => null, // Simplificado
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $planes,
                'message' => 'Planes de apadrinamiento obtenidos'
            ]);
        } catch (\Exception $e) {
            Log::error('Error en planes:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener planes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de un plan específico
     * GET /api/suscripciones/planes/{id}
     */
    public function planDetalle(int $id)
    {
        try {
            $mascota = Mascota::with(['fundacion' => function ($q) {
                $q->select('id', 'Nombre_1', 'imagen_portada', 'ciudad', 'descripcion');
            }])
                ->where('estado', 'En adopcion')
                ->select('id', 'nombre_mascota as nombre', 'especie', 'raza', 'edad_aprox as edad', 'descripcion', 'fundacion_id', 'foto_principal as imagen')
                ->findOrFail($id);

            $plan = [
                'id' => $mascota->id,
                'nombre' => $mascota->nombre ?? 'Sin nombre',
                'especie' => $mascota->especie ?? 'No especificada',
                'raza' => $mascota->raza ?? 'No especificada',
                'edad' => $mascota->edad ?? 0,
                'descripcion' => $mascota->descripcion ?? '',
                'imagen' => $mascota->imagen ?? null,
                'monto_recomendado' => 10000,
                'fundacion' => $mascota->fundacion ? [
                    'id' => $mascota->fundacion->id,
                    'nombre' => $mascota->fundacion->Nombre_1 ?? 'Fundación',
                    'imagen' => $mascota->fundacion->imagen_portada ?? null,
                    'ciudad' => $mascota->fundacion->ciudad ?? '',
                    'descripcion' => $mascota->fundacion->descripcion ?? '',
                ] : null,
            ];

            return response()->json([
                'success' => true,
                'data' => $plan,
                'message' => 'Plan obtenido'
            ]);
        } catch (\Exception $e) {
            Log::error('Error en planDetalle:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Plan no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * ✅ CREAR SUSCRIPCIÓN (Usuario autenticado)
     * POST /api/suscripciones/user/crear
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

            // Verificar si ya tiene una suscripción activa
            $existe = Suscripcion::where('user_id', $user->id)
                ->where('mascota_id', $request->mascota_id)
                ->whereIn('estado', ['activo', 'pausado', 'pendiente'])
                ->exists();

            if ($existe) {
                return $this->errorResponse('Ya tienes una suscripción activa o pendiente para esta mascota', null, 400);
            }

            // ✅ fecha_inicio = hoy
            $suscripcion = Suscripcion::create([
                'user_id' => $user->id,
                'mascota_id' => $request->mascota_id,
                'monto_mensual' => $request->monto_mensual,
                'frecuencia' => $request->frecuencia,
                'mensaje_apoyo' => $request->mensaje_apoyo,
                'fecha_inicio' => now(),
                'estado' => 'pendiente',
                'es_demo' => true,
                'payment_method' => null,
                'payment_reference' => null,
            ]);

            return $this->successResponse([
                'suscripcion' => $suscripcion->load(['mascota', 'user']),
                'mensaje' => 'Suscripción creada. Completa el pago para activarla.',
                'next_step' => 'payment',
            ], 'Suscripción creada - Pendiente de pago', 201);
        } catch (\Exception $e) {
            Log::error('Error al crear suscripción:', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error al crear la suscripción', $e->getMessage(), 500);
        }
    }

    /**
     * Obtener mis suscripciones (USUARIO AUTENTICADO)
     * GET /api/suscripciones/user/mis-suscripciones
     */
    public function misSuscripciones(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            Log::info('📋 Obteniendo suscripciones para usuario:', ['user_id' => $user->id]);

            // ✅ Obtener suscripciones CON los datos de la mascota
            $suscripciones = Suscripcion::where('user_id', $user->id)
                ->with(['mascota' => function ($query) {
                    // ✅ Seleccionar SOLO los campos que existen en tu tabla mascotas
                    $query->select(
                        'id',
                        'nombre_mascota',
                        'especie',
                        'raza',
                        'edad_aprox as edad',
                        'foto_principal',
                        'imagen_url',
                        'descripcion',
                        'estado as mascota_estado'
                    );
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('📊 Total suscripciones encontradas:', ['count' => $suscripciones->count()]);

            // ✅ Log para depuración - verificar si la mascota se cargó
            foreach ($suscripciones as $suscripcion) {
                Log::info('🐾 Suscripción:', [
                    'id' => $suscripcion->id,
                    'mascota_id' => $suscripcion->mascota_id,
                    'tiene_mascota' => $suscripcion->relationLoaded('mascota') ? 'Sí' : 'No',
                    'mascota_nombre' => $suscripcion->mascota->nombre_mascota ?? 'Sin nombre'
                ]);
            }

            // ✅ Transformar los datos para el frontend
            $data = $suscripciones->map(function ($suscripcion) {
                return [
                    'id' => $suscripcion->id,
                    'user_id' => $suscripcion->user_id,
                    'mascota_id' => $suscripcion->mascota_id,
                    'monto_mensual' => $suscripcion->monto_mensual,
                    'frecuencia' => $suscripcion->frecuencia,
                    'estado' => $suscripcion->estado,
                    'mensaje_apoyo' => $suscripcion->mensaje_apoyo,
                    'fecha_inicio' => $suscripcion->fecha_inicio,
                    'fecha_fin' => $suscripcion->fecha_fin,
                    'created_at' => $suscripcion->created_at,
                    'updated_at' => $suscripcion->updated_at,
                    // ✅ Incluir datos de la mascota
                    'mascota' => $suscripcion->mascota ? [
                        'id' => $suscripcion->mascota->id,
                        'nombre_mascota' => $suscripcion->mascota->nombre_mascota ?? 'Sin nombre',
                        'especie' => $suscripcion->mascota->especie ?? 'No especificada',
                        'raza' => $suscripcion->mascota->raza ?? 'No especificada',
                        'edad' => $suscripcion->mascota->edad ?? 0,
                        'foto_principal' => $suscripcion->mascota->foto_principal ?? null,
                        'imagen_url' => $suscripcion->mascota->imagen_url ?? null,
                        'descripcion' => $suscripcion->mascota->descripcion ?? '',
                        'estado' => $suscripcion->mascota->mascota_estado ?? 'En adopcion',
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Tus suscripciones obtenidas'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error en misSuscripciones:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener suscripciones: ' . $e->getMessage()
            ], 500);
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
