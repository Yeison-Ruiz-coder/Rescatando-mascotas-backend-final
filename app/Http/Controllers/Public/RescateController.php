<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Rescate;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RescateController extends Controller
{
    /**
     * Listado de rescates exitosos
     */
    public function index(Request $request)
    {
        $query = Rescate::with(['mascota', 'entidadResponsable']) // ✅ Cambiado
                       ->where('estado', 'completado');

        if ($request->filled('ano')) {
            $query->whereYear('fecha_rescate', $request->ano);
        }

        $rescates = $query->latest('fecha_rescate')->paginate(12);

        return view('public.rescates.index', compact('rescates'));
    }

    /**
     * Detalle de rescate
     */
    public function show($id)
    {
        $rescate = Rescate::with(['mascota', 'reporte', 'usuarioReporto', 'entidadResponsable', 'gestionadoPor']) // ✅ Cambiado
                         ->findOrFail($id);

        return view('public.rescates.show', compact('rescate'));
    }

    /**
     * Reportar un animal para rescate
     */
    public function create()
    {
        return view('public.rescates.create');
    }

    /**
     * Guardar reporte de rescate
     */
    public function guardarReporte(Request $request)
    {
        $request->validate([
            'tipo_reporte' => 'required|in:perdido,encontrado,maltrato',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'ubicacion' => 'required|string',
            'fecha_incidente' => 'required|date',
            'especie' => 'nullable|string',
            'raza' => 'nullable|string',
            'color' => 'nullable|string',
            'foto_url' => 'nullable|image|max:2048',
            'nombre_reportante' => 'required|string',
            'telefono_reportante' => 'required|string',
            'email_reportante' => 'required|email',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->except('foto_url');
            $data['estado'] = 'activo';
            $data['user_id'] = auth()->check() ? auth()->id() : null;

            if ($request->hasFile('foto_url')) {
                $path = $request->file('foto_url')->store('reportes', 'public');
                $data['foto_url'] = $path;
            }

            $reporte = Reporte::create($data);

            DB::commit();

            return redirect()->route('public.rescates.reporte-exitoso', $reporte->id)
                            ->with('success', 'Reporte enviado, pronto nos contactaremos');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al enviar reporte');
        }
    }

    /**
     * Confirmación de reporte
     */
    public function reporteExitoso($id)
    {
        $reporte = Reporte::findOrFail($id);
        return view('public.rescates.reporte-exito', compact('reporte'));
    }
}
