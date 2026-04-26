<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Suscripcion;
use App\Models\Mascota;
use Illuminate\Http\Request;

class SuscripcionController extends Controller
{
    // 📌 LISTAR
    public function index()
    {
        return Suscripcion::with(['user', 'mascota'])->get();
    }

    // 📌 CREAR
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'mascota_id' => 'required|exists:mascotas,id',
            'monto_mensual' => 'required|numeric|min:1',
            'frecuencia' => 'required|in:unica,mensual,trimestral,anual',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'mensaje_apoyo' => 'nullable|string',
            'estado' => 'required|in:activo,pausado,cancelado,finalizado',
        ]);

        $suscripcion = Suscripcion::create($validated);

        return response()->json([
            'message' => 'Suscripción creada correctamente',
            'data' => $suscripcion
        ], 201);
    }

    // 📌 MOSTRAR
    public function show(int $id)  // ← Agregado tipo int
    {
        return Suscripcion::with(['user', 'mascota'])->findOrFail($id);
    }

    // 📌 ACTUALIZAR
    public function update(Request $request, int $id)  // ← Agregado tipo int
    {
        $suscripcion = Suscripcion::findOrFail($id);
        $suscripcion->update($request->all());

        return response()->json([
            'message' => 'Suscripción actualizada',
            'data' => $suscripcion
        ]);
    }

    // 📌 ELIMINAR
    public function destroy(int $id)  // ← Agregado tipo int
    {
        $suscripcion = Suscripcion::findOrFail($id);
        $suscripcion->delete();

        return response()->json([
            'message' => 'Suscripción eliminada'
        ]);
    }

    // ============ MÉTODOS PÚBLICOS PARA EL FRONTEND ============
    
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
        
        return response()->json($planes);
    }

    /**
     * Obtener mascotas disponibles para apadrinar (público)
     * GET /api/public/mascotas-para-apadrinar
     */
    public function getMascotasApadrinar()
    {
        // Verificar si la tabla Mascota existe
        if (class_exists('App\Models\Mascota')) {
            $mascotas = Mascota::where('estado', 'disponible')
                ->orWhere('estado', 'activo')
                ->take(10)
                ->get()
                ->map(function ($mascota) {
                    return [
                        'id' => $mascota->id,
                        'nombre' => $mascota->nombre,
                        'especie' => $mascota->especie ?? 'Mascota',
                        'raza' => $mascota->raza ?? 'No especificada',
                        'edad' => $mascota->edad ?? 1,
                        'historia_corta' => $mascota->historia_corta ?? "{$mascota->nombre} necesita un padrino que lo ayude con sus cuidados.",
                        'monto_sugerido' => 10000,
                        'apadrinamientos' => 0,
                        'foto_url' => $mascota->foto_url ?? null,
                        'created_at' => $mascota->created_at,
                    ];
                });
        } else {
            // Datos de prueba por si no existe la tabla
            $mascotas = [
                [
                    'id' => 1,
                    'nombre' => 'Max',
                    'especie' => 'Perro',
                    'raza' => 'Golden Retriever',
                    'edad' => 3,
                    'historia_corta' => 'Max fue rescatado de la calle y necesita cuidados especiales.',
                    'monto_sugerido' => 15000,
                    'apadrinamientos' => 2,
                    'foto_url' => null,
                    'created_at' => now(),
                ],
                [
                    'id' => 2,
                    'nombre' => 'Luna',
                    'especie' => 'Gato',
                    'raza' => 'Siamés',
                    'edad' => 2,
                    'historia_corta' => 'Luna es muy cariñosa y busca un hogar temporal.',
                    'monto_sugerido' => 12000,
                    'apadrinamientos' => 1,
                    'foto_url' => null,
                    'created_at' => now(),
                ],
                [
                    'id' => 3,
                    'nombre' => 'Toby',
                    'especie' => 'Perro',
                    'raza' => 'Labrador',
                    'edad' => 4,
                    'historia_corta' => 'Toby necesita una operación y tu ayuda es vital.',
                    'monto_sugerido' => 20000,
                    'apadrinamientos' => 0,
                    'foto_url' => null,
                    'created_at' => now(),
                ],
            ];
        }
        
        return response()->json($mascotas);
    }

    /**
     * Crear suscripción pública (desde el frontend)
     * POST /api/public/suscripciones
     */
    public function storePublic(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'mascota_id' => 'nullable|exists:mascotas,id',
            'monto_mensual' => 'required|numeric|min:1000',
            'frecuencia' => 'required|in:unica,mensual,trimestral,anual',
            'fecha_inicio' => 'required|date',
            'mensaje_apoyo' => 'nullable|string',
            'estado' => 'required|in:activo,pausado,cancelado,finalizado',
            'plan_id' => 'nullable|integer'
        ]);

        $suscripcion = Suscripcion::create($validated);

        return response()->json([
            'message' => 'Suscripción creada correctamente',
            'data' => $suscripcion
        ], 201);
    }

    /**
     * Obtener suscripciones del usuario autenticado
     * GET /api/user/mis-suscripciones
     */
    public function getUserSuscripciones(Request $request)
    {
        $user = $request->user();
        $suscripciones = Suscripcion::where('user_id', $user->id)
            ->with(['mascota'])
            ->get();
        
        return response()->json($suscripciones);
    }

    /**
     * Cancelar suscripción de usuario
     * POST /api/user/suscripciones/{id}/cancelar
     */
    public function cancelarSuscripcion(int $id, Request $request)  // ← Agregado tipo int
    {
        $suscripcion = Suscripcion::findOrFail($id);
        
        // Verificar que la suscripción pertenece al usuario
        if ($suscripcion->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        $suscripcion->update(['estado' => 'cancelado']);
        
        return response()->json([
            'message' => 'Suscripción cancelada correctamente'
        ]);
    }
}