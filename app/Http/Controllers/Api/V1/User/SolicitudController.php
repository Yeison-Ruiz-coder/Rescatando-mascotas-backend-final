<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SolicitudController extends Controller
{
    /**
     * Listado de solicitudes del usuario
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Solicitud::where('user_id', $user->id)
            ->with('solicitable');

        // Filtro por tipo
        if ($request->has('tipo')) {
            $query->where('tipo_solicitud', $request->tipo);
        }

        // Filtro por estado
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        $solicitudes = $query->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $solicitudes
        ]);
    }

    /**
     * Detalle de solicitud
     */
    public function show($id)
    {
        $user = request()->user();

        $solicitud = Solicitud::where('user_id', $user->id)
            ->with(['solicitable', 'revisor'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $solicitud
        ]);
    }

    /**
     * Crear solicitud de adopción
     */
    public function storeAdopcion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mascota_id' => 'required|exists:mascotas,id',
            'motivo_adopcion' => 'required|string|min:20',
            'experiencia_mascotas' => 'required|string',
            'tipo_vivienda' => 'required|string',
            'direccion' => 'required|string',
            'compromiso_cuidado' => 'required|boolean',
            'compromiso_esterilizacion' => 'required|boolean',
            'compromiso_seguimiento' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $mascota = Mascota::find($request->mascota_id);

        if ($mascota->estado !== 'En adopcion') {
            return response()->json([
                'success' => false,
                'message' => 'Esta mascota no está disponible para adopción'
            ], 400);
        }

        // Verificar si ya tiene una solicitud pendiente
        $existe = Solicitud::where('user_id', $request->user()->id)
            ->where('solicitable_type', 'App\\Models\\Mascota')
            ->where('solicitable_id', $request->mascota_id)
            ->whereIn('estado', ['pendiente', 'en_revision'])
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes una solicitud pendiente para esta mascota'
            ], 400);
        }

        $solicitud = Solicitud::create([
            'tipo_solicitud' => 'adopcion',
            'contenido' => $request->motivo_adopcion,
            'fecha_solicitud' => now(),
            'estado' => 'pendiente',
            'user_id' => $request->user()->id,
            'nombre_solicitante' => $request->user()->nombre,
            'email_solicitante' => $request->user()->email,
            'telefono_solicitante' => $request->user()->telefono,
            'solicitable_id' => $request->mascota_id,
            'solicitable_type' => 'App\\Models\\Mascota',
        ]);

        // Guardar datos adicionales
        $solicitud->setDatosAdopcion([
            'experiencia_mascotas' => $request->experiencia_mascotas,
            'tipo_vivienda' => $request->tipo_vivienda,
            'motivo_adopcion' => $request->motivo_adopcion,
            'direccion' => $request->direccion,
            'compromiso_cuidado' => $request->compromiso_cuidado,
            'compromiso_esterilizacion' => $request->compromiso_esterilizacion,
            'compromiso_seguimiento' => $request->compromiso_seguimiento,
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de adopción enviada exitosamente',
            'data' => $solicitud->load('solicitable')
        ], 201);
    }

    /**
     * Cancelar solicitud (solo si está pendiente)
     */
    public function cancelar($id)
    {
        $user = request()->user();

        $solicitud = Solicitud::where('user_id', $user->id)
            ->whereIn('estado', ['pendiente', 'en_revision'])
            ->findOrFail($id);

        $solicitud->update([
            'estado' => 'cancelada',
            'fecha_revision' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud cancelada exitosamente'
        ]);
    }
}
