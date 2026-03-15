<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rescate;
use App\Models\Mascota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RescateController extends Controller
{
    /**
     * Listado de rescates
     */
    public function index(Request $request)
    {
        $query = Rescate::with(['mascota', 'reporte', 'entidadResponsable', 'gestionadoPor']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $rescates = $query->latest()->paginate(20);

        return view('admin.rescates.index', compact('rescates'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $mascotas = Mascota::whereNull('fundacion_id')->get();

        return view('admin.rescates.create', compact('mascotas'));
    }

    /**
     * Guardar rescate
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha_rescate' => 'required|date',
            'lugar_rescate' => 'required|string',
            'descripcion_rescate' => 'required|string',
            'estado' => 'required|in:en_proceso,completado,seguimiento',
            'mascota_id' => 'nullable|exists:mascotas,id',
            'reporte_id' => 'nullable|exists:reportes,id',
            'entidad_responsable_id' => 'nullable',
            'entidad_responsable_type' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['gestionado_por'] = auth()->id();

        Rescate::create($data);

        return redirect()->route('admin.rescates.index')
                        ->with('success', 'Rescate registrado');
    }

    /**
     * Mostrar rescate
     */
    public function show($id)
    {
        $rescate = Rescate::with(['mascota', 'reporte', 'usuarioReporto', 'entidadResponsable', 'gestionadoPor'])
                          ->findOrFail($id);

        return view('admin.rescates.show', compact('rescate'));
    }

    /**
     * Formulario de edición
     */
    public function edit($id)
    {
        $rescate = Rescate::findOrFail($id);

        return view('admin.rescates.edit', compact('rescate'));
    }

    /**
     * Actualizar rescate
     */
    public function update(Request $request, $id)
    {
        $rescate = Rescate::findOrFail($id);

        $request->validate([
            'fecha_rescate' => 'required|date',
            'lugar_rescate' => 'required|string',
            'descripcion_rescate' => 'required|string',
            'estado' => 'required|in:en_proceso,completado,seguimiento',
            'entidad_responsable_id' => 'nullable',
            'entidad_responsable_type' => 'nullable|string',
        ]);

        $rescate->update($request->all());

        return redirect()->route('admin.rescates.show', $id)
                        ->with('success', 'Rescate actualizado');
    }

    /**
     * Eliminar rescate
     */
    public function destroy($id)
    {
        $rescate = Rescate::findOrFail($id);
        $rescate->delete();

        return redirect()->route('admin.rescates.index')
                        ->with('success', 'Rescate eliminado');
    }

    /**
     * Completar rescate (asignar a fundación)
     */
    public function completar(Request $request, $id)
    {
        $rescate = Rescate::findOrFail($id);

        $request->validate([
            'fundacion_id' => 'required|exists:fundaciones,id',
            'nombre_mascota' => 'required|string',
            'especie' => 'required|string',
        ]);

        DB::beginTransaction();

        try {
            // Crear mascota a partir del rescate
            $mascota = Mascota::create([
                'nombre_mascota' => $request->nombre_mascota,
                'especie' => $request->especie,
                'estado' => 'Rescatada',
                'lugar_rescate' => $rescate->lugar_rescate,
                'descripcion' => "Rescatada el " . $rescate->fecha_rescate . ". " . $rescate->descripcion_rescate,
                'fecha_ingreso' => now(),
                'fundacion_id' => $request->fundacion_id,
            ]);

            $rescate->update([
                'mascota_id' => $mascota->id,
                'estado' => 'completado',
            ]);

            DB::commit();

            return redirect()->route('admin.mascotas.show', $mascota->id)
                            ->with('success', 'Rescate completado. Mascota creada.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al completar rescate');
        }
    }
}
