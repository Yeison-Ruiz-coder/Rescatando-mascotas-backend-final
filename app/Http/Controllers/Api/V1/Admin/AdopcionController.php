<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Adopcion;
use App\Models\Mascota;
use App\Models\User;
use App\Models\Fundacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdopcionController extends Controller
{
    /**
     * Listado de adopciones
     */
    public function index(Request $request)
    {
        $query = Adopcion::with(['adoptante', 'mascota', 'fundacion', 'administrador', 'solicitud']);

        // Filtros
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('fundacion_id')) {
            $query->where('fundacion_id', $request->fundacion_id);
        }

        if ($request->has('fecha_desde')) {
            $query->whereDate('fecha_adopcion', '>=', $request->fecha_desde);
        }

        if ($request->has('fecha_hasta')) {
            $query->whereDate('fecha_adopcion', '<=', $request->fecha_hasta);
        }

        $adopciones = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $adopciones
        ]);
    }

    /**
     * Crear adopción
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_adopcion' => 'nullable|date',
            'estado' => 'required|in:en_proceso,aprobada,completada,rechazada,cancelada',
            'razon_rechazo' => 'nullable|string|max:500',
            'solicitud_id' => 'nullable|exists:solicitudes,id',
            'user_id' => 'required|exists:users,id',
            'mascota_id' => 'required|exists:mascotas,id',
            'fundacion_id' => 'nullable|exists:fundaciones,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $data = $request->all();
            $data['administrador_id'] = auth()->id();

            if (empty($data['fecha_adopcion'])) {
                $data['fecha_adopcion'] = now();
            }

            $adopcion = Adopcion::create($data);

            // Actualizar estado de la mascota
            $this->actualizarEstadoMascota($adopcion);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Adopción creada',
                'data' => $adopcion->load(['adoptante', 'mascota'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear adopción',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar adopción
     */
    public function show($id)
    {
        $adopcion = Adopcion::with(['adoptante', 'mascota', 'fundacion', 'administrador', 'solicitud', 'entrevistas', 'seguimientos'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $adopcion
        ]);
    }

    /**
     * Actualizar adopción
     */
    public function update(Request $request, $id)
    {
        $adopcion = Adopcion::findOrFail($id);
        $estadoAnterior = $adopcion->estado;

        $validator = Validator::make($request->all(), [
            'fecha_adopcion' => 'nullable|date',
            'estado' => 'sometimes|in:en_proceso,aprobada,completada,rechazada,cancelada',
            'razon_rechazo' => 'nullable|string|max:500',
            'user_id' => 'sometimes|exists:users,id',
            'mascota_id' => 'sometimes|exists:mascotas,id',
            'fundacion_id' => 'nullable|exists:fundaciones,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $adopcion->update($request->all());

            if ($estadoAnterior !== $adopcion->estado) {
                $this->actualizarEstadoMascota($adopcion);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Adopción actualizada',
                'data' => $adopcion->fresh(['adoptante', 'mascota'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar adopción
     */
    public function destroy($id)
    {
        $adopcion = Adopcion::findOrFail($id);

        DB::beginTransaction();

        try {
            if ($adopcion->seguimientos()->exists() || $adopcion->entrevistas()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar: tiene seguimientos o entrevistas'
                ], 400);
            }

            if ($adopcion->estado === 'completada' && $adopcion->mascota) {
                $adopcion->mascota->update(['estado' => 'En adopcion']);
            }

            $adopcion->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Adopción eliminada'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado rápidamente
     */
    public function cambiarEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:en_proceso,aprobada,completada,rechazada,cancelada',
            'razon_rechazo' => 'required_if:estado,rechazada,cancelada|nullable|string'
        ]);

        $adopcion = Adopcion::findOrFail($id);

        DB::beginTransaction();

        try {
            $adopcion->estado = $request->estado;

            if (in_array($request->estado, ['rechazada', 'cancelada'])) {
                $adopcion->razon_rechazo = $request->razon_rechazo;
                $adopcion->fecha_cierre = now();
            }

            if ($request->estado === 'completada') {
                $adopcion->fecha_cierre = now();
            }

            $adopcion->save();
            $this->actualizarEstadoMascota($adopcion);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado',
                'data' => $adopcion
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método auxiliar para actualizar estado de mascota
     */
    private function actualizarEstadoMascota($adopcion)
    {
        if (!$adopcion->mascota) {
            return;
        }

        switch ($adopcion->estado) {
            case 'completada':
                $adopcion->mascota->update(['estado' => 'Adoptado']);
                break;
            case 'en_proceso':
            case 'aprobada':
                $adopcion->mascota->update(['estado' => 'En proceso de adopción']);
                break;
            case 'rechazada':
            case 'cancelada':
                $adopcion->mascota->update(['estado' => 'En adopcion']);
                break;
        }
    }
}
