<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SuscripcionController extends Controller
{
    /**
     * Obtener planes de membresía (público)
     * GET /api/public/planes-membresia
     */
    public function getPlanesMembresia()
    {
        $planes = [
            [
                'id' => 1,
                'nombre' => 'Plan Básico',
                'monto' => 10000,
                'frecuencia' => 'mensual',
                'destacado' => false,
                'descripcion' => 'Ideal para empezar a ayudar',
                'beneficios' => ['Certificado digital', 'Actualización mensual', 'Calcomanía']
            ],
            [
                'id' => 2,
                'nombre' => 'Plan Premium',
                'monto' => 25000,
                'frecuencia' => 'mensual',
                'destacado' => true,
                'descripcion' => 'Para quienes quieren marcar la diferencia',
                'beneficios' => ['Certificado premium', 'Actualización semanal', 'Fotos exclusivas', 'Descuento en tienda', 'Eventos especiales']
            ],
            [
                'id' => 3,
                'nombre' => 'Plan Vitalicio',
                'monto' => 50000,
                'frecuencia' => 'mensual',
                'destacado' => false,
                'descripcion' => 'Para los súper patrocinadores',
                'beneficios' => ['Certificado especial', 'Visitas mensuales', 'Nombre en placa', 'Descuento 20% tienda', 'Eventos VIP']
            ]
        ];
        
        return response()->json([
            'success' => true,
            'data' => $planes
        ]);
    }

    /**
     * Obtener mascotas disponibles para apadrinar (público)
     * GET /api/public/mascotas-para-apadrinar
     */
    public function getMascotasApadrinar()
    {
        // Estados según tu base de datos
        $estadosValidos = ['En adopcion', 'Rescatada'];
        
        // Buscar mascotas
        $mascotas = Mascota::whereIn('estado', $estadosValidos)
            ->latest('created_at')
            ->take(10)
            ->get();
        
        if ($mascotas->count() > 0) {
            $resultado = $mascotas->map(function ($mascota) {
                // Imagen por defecto según especie
                $imagenPorDefecto = $mascota->especie == 'Perro' 
                    ? 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=300&h=250&fit=crop'
                    : ($mascota->especie == 'Gato' 
                        ? 'https://images.unsplash.com/photo-1574158622682-e40e69881006?w=300&h=250&fit=crop'
                        : 'https://images.unsplash.com/photo-1535241749838-299277b6305f?w=300&h=250&fit=crop');
                
                // Procesar la URL de la foto
                $fotoUrl = $imagenPorDefecto;
                if ($mascota->foto_principal) {
                    if (strpos($mascota->foto_principal, 'http') === 0) {
                        $fotoUrl = $mascota->foto_principal;
                    } else {
                        $fotoUrl = asset('storage/' . $mascota->foto_principal);
                    }
                }
                
                return [
                    'id' => $mascota->id,
                    'nombre' => $mascota->nombre_mascota,
                    'especie' => $mascota->especie,
                    'raza' => $mascota->raza ?? 'No especificada',
                    'edad' => $mascota->edad_aprox ?? 1,
                    'genero' => $mascota->genero ?? 'No especificado',
                    'historia_corta' => $mascota->descripcion ?? "{$mascota->nombre_mascota} necesita un padrino",
                    'lugar_rescate' => $mascota->lugar_rescate,
                    'condiciones_especiales' => $mascota->condiciones_especiales,
                    'monto_sugerido' => 15000,
                    'apadrinamientos' => Suscripcion::where('mascota_id', $mascota->id)->count(),
                    'foto_url' => $fotoUrl,
                    'created_at' => $mascota->created_at,
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        }
        
        // Si no hay mascotas, devolver array vacío
        return response()->json([
            'success' => true,
            'data' => []
        ]);
    }

    /**
     * Get estadísticas generales (público)
     * GET /api/public/suscripciones-estadisticas
     */
    public function estadisticas()
    {
        try {
            $totalSuscripciones = Suscripcion::count();
            $activas = Suscripcion::where('estado', 'activo')->count();
            $pausadas = Suscripcion::where('estado', 'pausado')->count();
            $canceladas = Suscripcion::where('estado', 'cancelado')->count();
            $finalizadas = Suscripcion::where('estado', 'finalizado')->count();
            $totalMensual = Suscripcion::where('estado', 'activo')->sum('monto_mensual');

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
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_suscripciones' => 0,
                    'activas' => 0,
                    'pausadas' => 0,
                    'canceladas' => 0,
                    'finalizadas' => 0,
                    'ingreso_mensual_total' => 0
                ]
            ]);
        }
    }

    /**
     * Crear suscripción pública
     * POST /api/public/suscripciones-crear
     */
    public function storePublic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'mascota_id' => 'nullable|exists:mascotas,id',
            'monto_mensual' => 'required|numeric|min:1000',
            'frecuencia' => 'required|in:unica,mensual,trimestral,anual',
            'fecha_inicio' => 'required|date',
            'mensaje_apoyo' => 'nullable|string',
            'estado' => 'required|in:activo,pausado,cancelado,finalizado',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $suscripcion = new Suscripcion();
            $suscripcion->user_id = $request->user_id;
            $suscripcion->mascota_id = $request->mascota_id;
            $suscripcion->monto_mensual = $request->monto_mensual;
            $suscripcion->frecuencia = $request->frecuencia;
            $suscripcion->fecha_inicio = $request->fecha_inicio;
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
     * Obtener suscripciones del usuario
     * GET /api/public/suscripciones/usuario/{userId}
     */
    public function getUserSuscripciones(int $userId)
    {
        try {
            $suscripciones = Suscripcion::where('user_id', $userId)
                ->with(['mascota.fundacion'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $suscripciones
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener suscripciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelar suscripción
     * POST /api/public/suscripciones/{id}/cancelar
     */
    public function cancelarSuscripcion(int $id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $suscripcion = Suscripcion::where('id', $id)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$suscripcion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Suscripción no encontrada'
                ], 404);
            }

            $suscripcion->estado = 'cancelado';
            $suscripcion->save();

            return response()->json([
                'success' => true,
                'message' => 'Suscripción cancelada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la suscripción: ' . $e->getMessage()
            ], 500);
        }
    }
}