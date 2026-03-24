<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Models\Rescate;
use App\Models\Mascota;
use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RescateController extends Controller
{
    /**
     * Ver rescates disponibles cerca de mi entidad
     */
    public function disponibles(Request $request)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $lat = $entidad->lat ?? null;
        $lng = $entidad->lng ?? null;
        $radio = $entidad->radio_atencion ?? 10;

        // 🔥 CORREGIDO: Guardamos el tipo en una variable para usar en el closure
        $userTipo = $user->tipo;

        $rescates = Rescate::where('estado', 'pendiente')
            ->where(function($query) use ($userTipo) {
                if ($userTipo === 'veterinaria') {
                    $query->whereIn('tipo_emergencia', ['herido', 'urgente']);
                } else {
                    $query->whereIn('tipo_emergencia', ['abandonado', 'urgente']);
                }
            });

        if ($lat && $lng) {
            $rescates = $rescates->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", [$lat, $lng, $lat])
                ->having('distance', '<', $radio)
                ->orderBy('distance');
        }

        $rescates = $rescates->orderBy('prioridad', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $rescates
        ]);
    }

    /**
     * Aceptar un rescate
     */
    public function aceptar($id)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $rescate = Rescate::where('estado', 'pendiente')
            ->findOrFail($id);

        // Verificar que la entidad puede aceptar este tipo de rescate
        $puedeAceptar = false;
        if ($user->tipo === 'veterinaria' && in_array($rescate->tipo_emergencia, ['herido', 'urgente'])) {
            $puedeAceptar = true;
        }
        if ($user->tipo === 'fundacion' && in_array($rescate->tipo_emergencia, ['abandonado', 'urgente'])) {
            $puedeAceptar = true;
        }

        if (!$puedeAceptar) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes aceptar este tipo de rescate'
            ], 403);
        }

        DB::beginTransaction();
        try {
            $rescate->update([
                'estado' => 'en_proceso',
                'entidad_responsable_type' => get_class($entidad),
                'entidad_responsable_id' => $entidad->id,
            ]);

            // Notificar al usuario que reportó
            if ($rescate->usuario_reporto_id) {
                $nombreEntidad = $user->tipo === 'veterinaria' ? ($entidad->Nombre_vet ?? $entidad->nombre) : ($entidad->Nombre_1 ?? $entidad->nombre);

                Notificacion::create([
                    'user_id' => $rescate->usuario_reporto_id,
                    'contenido' => "Tu reporte de rescate fue aceptado por {$nombreEntidad}",
                    'creado_por_id' => $user->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rescate aceptado exitosamente',
                'data' => $rescate->load(['usuarioReporto', 'entidadResponsable'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al aceptar el rescate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rechazar un rescate
     */
    public function rechazar($id)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $rescate = Rescate::where('estado', 'pendiente')
            ->findOrFail($id);

        $rescate->update([
            'estado' => 'pendiente' // Sigue pendiente para otras entidades
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rescate rechazado'
        ]);
    }

    /**
     * Listar rescates de mi entidad
     */
    public function misRescates(Request $request)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $rescates = Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->with(['usuarioReporto', 'mascota'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $rescates
        ]);
    }

    /**
     * Registrar mascota desde un rescate
     */
    public function registrarMascota(Request $request, $id)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $rescate = Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->where('estado', 'en_proceso')
            ->findOrFail($id);

        $validated = $request->validate([
            'nombre_mascota' => 'required|string|max:255',
            'especie' => 'required|string|in:Perro,Gato,Conejo,Otro',
            'edad_aprox' => 'nullable|integer|min:0|max:30',
            'genero' => 'nullable|in:Macho,Hembra,Desconocido',
            'descripcion' => 'nullable|string',
            'foto_principal' => 'nullable|image|max:2048',
            'necesita_hogar_temporal' => 'boolean',
            'apto_con_ninos' => 'boolean',
            'apto_con_otros_animales' => 'boolean',
            'condiciones_especiales' => 'nullable|string',
            'fecha_ingreso' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $mascota = Mascota::create([
                'nombre_mascota' => $validated['nombre_mascota'],
                'especie' => $validated['especie'],
                'edad_aprox' => $validated['edad_aprox'] ?? null,
                'genero' => $validated['genero'] ?? null,
                'descripcion' => $validated['descripcion'] ?? null,
                'necesita_hogar_temporal' => $validated['necesita_hogar_temporal'] ?? false,
                'apto_con_ninos' => $validated['apto_con_ninos'] ?? true,
                'apto_con_otros_animales' => $validated['apto_con_otros_animales'] ?? true,
                'condiciones_especiales' => $validated['condiciones_especiales'] ?? null,
                'fecha_ingreso' => $validated['fecha_ingreso'],
                'estado' => ($validated['necesita_hogar_temporal'] ?? false) ? 'En acogida' : 'En adopcion',
                'fundacion_id' => $user->tipo === 'fundacion' ? $entidad->id : null,
                'lugar_rescate' => $rescate->lugar_rescate,
            ]);

            // Subir foto si existe
            if ($request->hasFile('foto_principal')) {
                $path = $request->file('foto_principal')->store('mascotas', 'public');
                $mascota->update(['foto_principal' => $path]);
            }

            // Actualizar rescate con la mascota
            $rescate->update([
                'mascota_id' => $mascota->id,
                'estado' => 'completado'
            ]);

            // Notificar al usuario que reportó
            if ($rescate->usuario_reporto_id) {
                Notificacion::create([
                    'user_id' => $rescate->usuario_reporto_id,
                    'contenido' => "La mascota que reportaste ({$mascota->nombre_mascota}) ha sido registrada y está en proceso de adopción",
                    'creado_por_id' => $user->id,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mascota registrada exitosamente',
                'data' => $mascota->load(['razas', 'vacunas'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar mascota: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener la entidad asociada al usuario
     */
    private function getEntidad($user)
    {
        if ($user->tipo === 'veterinaria') {
            return $user->veterinaria;
        }
        if ($user->tipo === 'fundacion') {
            return $user->fundacion;
        }
        return null;
    }
}
