<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use App\Models\Mascota;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    /**
     * Formulario de solicitud general
     */
    public function create()
    {
        $tipos = [
            'adopcion' => 'Adopción',
            'rescate' => 'Rescate',
            'apadrinamiento' => 'Apadrinamiento',
            'donacion' => 'Donación',
            'otro' => 'Otro'
        ];

        return view('public.solicitudes.create', compact('tipos'));
    }

    /**
     * Guardar solicitud general
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipo_solicitud' => 'required|in:adopcion,rescate,apadrinamiento,donacion,otro',
            'contenido' => 'required|string',
            'nombre_solicitante' => 'required|string',
            'email_solicitante' => 'required|email',
            'telefono_solicitante' => 'required|string',
            'datos_adicionales' => 'nullable|array',
        ]);

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

        return redirect()->route('public.solicitudes.gracias', $solicitud->id)
                        ->with('success', 'Solicitud enviada');
    }

    /**
     * Página de agradecimiento
     */
    public function gracias($id)
    {
        $solicitud = Solicitud::findOrFail($id);
        return view('public.solicitudes.gracias', compact('solicitud'));
    }
}
