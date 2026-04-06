<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolicitudController extends Controller
{
    /**
     * Listar solicitudes del usuario autenticado
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            $solicitudes = Solicitud::with(['solicitable'])
                ->where('user_id', $user->id)
                ->where('tipo_solicitud', 'adopcion')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $solicitudes
            ]);

        } catch (\Exception $e) {
            Log::error('Error al cargar solicitudes: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar las solicitudes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ver detalle de una solicitud
     */
    public function show($id)
    {
        try {
            $solicitud = Solicitud::with(['solicitable'])
                ->where('user_id', auth()->id())
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $solicitud
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ], 404);
        }
    }

    /**
     * Crear solicitud de adopción - CORREGIDO
     */
    public function storeAdopcion(Request $request)
    {
        try {
            // Validación
            $validated = $request->validate([
                'mascota_id' => 'required|exists:mascotas,id',
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'email' => 'required|email',
                'telefono' => 'required|string|max:20',
                'documento_identidad' => 'required|string|max:20',
                'direccion' => 'required|string',
                'ciudad' => 'nullable|string',
                'departamento' => 'nullable|string',
                'codigo_postal' => 'nullable|string',
                'estado_civil' => 'nullable|string',
                'cantidad_hijos' => 'nullable|string',
                'ocupacion' => 'nullable|string',
                'experiencia_mascotas' => 'required|string',
                'tipo_vivienda' => 'required|string',
                'es_propietario' => 'nullable|string',
                'motivo_adopcion' => 'required|string|min:10',
                'compromiso_cuidado' => 'required|boolean',
                'compromiso_esterilizacion' => 'required|boolean',
                'compromiso_seguimiento' => 'required|boolean',
            ]);

            // Verificar mascota
            $mascota = Mascota::findOrFail($request->mascota_id);

            if ($mascota->estado !== 'En adopcion') {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta mascota ya no está disponible para adopción'
                ], 400);
            }

            DB::beginTransaction();

            // Preparar datos adicionales como JSON
            $datosAdicionales = [
                'apellido_solicitante' => $request->apellido,
                'documento_identidad' => $request->documento_identidad,
                'direccion' => $request->direccion,
                'ciudad' => $request->ciudad,
                'departamento' => $request->departamento,
                'codigo_postal' => $request->codigo_postal,
                'estado_civil' => $request->estado_civil,
                'cantidad_hijos' => $request->cantidad_hijos,
                'ocupacion' => $request->ocupacion,
                'experiencia_mascotas' => $request->experiencia_mascotas,
                'tipo_vivienda' => $request->tipo_vivienda,
                'es_propietario' => $request->es_propietario,
                'compromiso_cuidado' => $request->compromiso_cuidado,
                'compromiso_esterilizacion' => $request->compromiso_esterilizacion,
                'compromiso_seguimiento' => $request->compromiso_seguimiento,
            ];

            // ✅ CREAR SOLICITUD CON LOS NOMBRES CORRECTOS DE COLUMNAS
            $solicitud = Solicitud::create([
                'tipo_solicitud' => 'adopcion',
                'contenido' => $request->motivo_adopcion,
                'estado' => 'pendiente',
                'user_id' => auth()->id(),
                'nombre_solicitante' => $request->nombre,        // ← IMPORTANTE: nombre_solicitante
                'email_solicitante' => $request->email,          // ← IMPORTANTE: email_solicitante
                'telefono_solicitante' => $request->telefono,    // ← IMPORTANTE: telefono_solicitante
                'solicitable_type' => Mascota::class,            // ← IMPORTANTE: solicitable_type
                'solicitable_id' => $mascota->id,                // ← IMPORTANTE: solicitable_id
                'datos_adicionales' => $datosAdicionales,
            ]);

            DB::commit();

            // Log para depuración
            Log::info('Solicitud creada exitosamente', [
                'id' => $solicitud->id,
                'nombre_solicitante' => $solicitud->nombre_solicitante,
                'email_solicitante' => $solicitud->email_solicitante,
                'solicitable_type' => $solicitud->solicitable_type,
                'solicitable_id' => $solicitud->solicitable_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Solicitud enviada exitosamente',
                'data' => $solicitud
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear solicitud: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }
}
