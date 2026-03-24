<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SolicitudController extends Controller
{
    /**
     * POST /api/solicitudes
     * Crear solicitud general (sin autenticación)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_solicitud' => 'required|in:adopcion,rescate,apadrinamiento,donacion,otro',
            'contenido' => 'required|string|min:10',
            'nombre_solicitante' => 'required|string|max:255',
            'email_solicitante' => 'required|email|max:255',
            'telefono_solicitante' => 'required|string|max:20',
            'datos_adicionales' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $solicitud = Solicitud::create([
            'tipo_solicitud' => $request->tipo_solicitud,
            'contenido' => $request->contenido,
            'fecha_solicitud' => now(),
            'estado' => 'pendiente',
            'user_id' => auth()->check() ? auth()->id() : null,
            'nombre_solicitante' => $request->nombre_solicitante,
            'email_solicitante' => $request->email_solicitante,
            'telefono_solicitante' => $request->telefono_solicitante,
        ]);

        // Guardar datos adicionales si existen
        if ($request->has('datos_adicionales')) {
            $solicitud->update([
                'datos_adicionales' => json_encode($request->datos_adicionales, JSON_UNESCAPED_UNICODE)
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Solicitud enviada exitosamente',
            'data' => [
                'id' => $solicitud->id,
                'tipo_solicitud' => $solicitud->tipo_solicitud,
                'estado' => $solicitud->estado,
                'created_at' => $solicitud->created_at
            ]
        ], 201);
    }
}
