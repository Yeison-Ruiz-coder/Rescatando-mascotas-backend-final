<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Rescate;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class RescateController extends Controller
{
    /**
     * Listado de rescates exitosos
     */
    public function index(Request $request)
    {
        $query = Rescate::with(['mascota', 'entidadResponsable'])
            ->where('estado', 'completado');

        if ($request->has('ano')) {
            $query->whereYear('fecha_rescate', $request->ano);
        }

        $rescates = $query->latest('fecha_rescate')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $rescates
        ]);
    }

    /**
     * Reportar un animal para rescate (PÚBLICO - SIN AUTH)
     */
    public function reportar(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

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

            return response()->json([
                'success' => true,
                'message' => 'Reporte enviado exitosamente',
                'data' => $reporte
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar reporte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detalle de rescate
     */
    public function show($id)
    {
        $rescate = Rescate::with(['mascota', 'reporte', 'usuarioReporto', 'entidadResponsable'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $rescate
        ]);
    }
}
