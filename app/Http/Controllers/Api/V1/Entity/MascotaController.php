<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Models\Mascota;
use App\Models\Rescate;
use App\Models\Raza;
use App\Models\TipoVacuna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MascotaController extends Controller
{
    /**
     * Listar mascotas de la entidad
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $query = Mascota::with(['razas', 'vacunas']);

        // Filtrar por fundación o veterinaria según el tipo
        if ($user->tipo === 'fundacion') {
            $query->where('fundacion_id', $entidad->id);
        } else {
            // Para veterinaria, buscamos mascotas que hayan sido atendidas
            $query->whereHas('historialMedico', function($q) use ($entidad) {
                $q->where('veterinaria_id', $entidad->id);
            });
        }

        // Filtros
        if ($request->estado) {
            $query->where('estado', $request->estado);
        }
        if ($request->especie) {
            $query->where('especie', $request->especie);
        }
        if ($request->search) {
            $query->where('nombre_mascota', 'LIKE', "%{$request->search}%");
        }

        $mascotas = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $mascotas
        ]);
    }

    /**
     * Registrar una nueva mascota
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $validated = $request->validate([
            'nombre_mascota' => 'required|string|max:255',
            'especie' => 'required|string|in:Perro,Gato,Conejo,Otro',
            'razas' => 'nullable|array',
            'razas.*' => 'exists:razas,id',
            'edad_aprox' => 'nullable|integer|min:0|max:30',
            'genero' => 'nullable|in:Macho,Hembra,Desconocido',
            'estado' => 'required|in:En adopcion,En acogida,Rescatada,Adoptado',
            'lugar_rescate' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'condiciones_especiales' => 'nullable|string',
            'necesita_hogar_temporal' => 'boolean',
            'apto_con_ninos' => 'boolean',
            'apto_con_otros_animales' => 'boolean',
            'vacunas' => 'nullable|array',
            'vacunas.*' => 'exists:tipos_vacunas,id',
            'fecha_ingreso' => 'required|date',
            'fecha_salida' => 'nullable|date|after_or_equal:fecha_ingreso',
            'rescate_id' => 'nullable|exists:rescates,id',
            'foto_principal' => 'nullable|image|max:2048',
            'galeria_fotos' => 'nullable|array',
            'galeria_fotos.*' => 'image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Crear mascota
            $mascota = Mascota::create([
                'nombre_mascota' => $validated['nombre_mascota'],
                'especie' => $validated['especie'],
                'edad_aprox' => $validated['edad_aprox'] ?? null,
                'genero' => $validated['genero'] ?? null,
                'estado' => $validated['estado'],
                'lugar_rescate' => $validated['lugar_rescate'] ?? null,
                'descripcion' => $validated['descripcion'] ?? null,
                'condiciones_especiales' => $validated['condiciones_especiales'] ?? null,
                'necesita_hogar_temporal' => $validated['necesita_hogar_temporal'] ?? false,
                'apto_con_ninos' => $validated['apto_con_ninos'] ?? true,
                'apto_con_otros_animales' => $validated['apto_con_otros_animales'] ?? true,
                'fecha_ingreso' => $validated['fecha_ingreso'],
                'fecha_salida' => $validated['fecha_salida'] ?? null,
                'fundacion_id' => $user->tipo === 'fundacion' ? $entidad->id : null,
            ]);

            // Subir foto principal
            if ($request->hasFile('foto_principal')) {
                $path = $request->file('foto_principal')->store('mascotas', 'public');
                $mascota->update(['foto_principal' => $path]);
            }

            // Subir galería de fotos
            if ($request->hasFile('galeria_fotos')) {
                $galeria = [];
                foreach ($request->file('galeria_fotos') as $foto) {
                    $path = $foto->store('mascotas/galeria', 'public');
                    $galeria[] = $path;
                }
                $mascota->update(['galeria_fotos' => json_encode($galeria)]);
            }

            // Asociar razas
            if (!empty($validated['razas'])) {
                $mascota->razas()->sync($validated['razas']);
            }

            // Asociar vacunas
            if (!empty($validated['vacunas'])) {
                $vacunasConFecha = [];
                foreach ($validated['vacunas'] as $vacunaId) {
                    $vacunasConFecha[$vacunaId] = ['fecha_aplicacion' => now()];
                }
                $mascota->vacunas()->sync($vacunasConFecha);
            }

            // Si viene de un rescate, actualizar
            if (!empty($validated['rescate_id'])) {
                $rescate = Rescate::find($validated['rescate_id']);
                if ($rescate) {
                    $rescate->update([
                        'mascota_id' => $mascota->id,
                        'estado' => 'completado'
                    ]);
                }
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
     * Ver detalle de una mascota
     */
    public function show($id)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $mascota = Mascota::with(['razas', 'vacunas', 'historialMedico', 'adopciones', 'solicitudes'])
            ->findOrFail($id);

        // Verificar que la mascota pertenece a la entidad
        if ($user->tipo === 'fundacion' && $mascota->fundacion_id !== $entidad->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $mascota
        ]);
    }

    /**
     * Actualizar una mascota
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $mascota = Mascota::findOrFail($id);

        // Verificar propiedad
        if ($user->tipo === 'fundacion' && $mascota->fundacion_id !== $entidad->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $validated = $request->validate([
            'nombre_mascota' => 'sometimes|string|max:255',
            'especie' => 'sometimes|string|in:Perro,Gato,Conejo,Otro',
            'razas' => 'nullable|array',
            'razas.*' => 'exists:razas,id',
            'edad_aprox' => 'nullable|integer|min:0|max:30',
            'genero' => 'nullable|in:Macho,Hembra,Desconocido',
            'estado' => 'sometimes|in:En adopcion,En acogida,Rescatada,Adoptado',
            'lugar_rescate' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'condiciones_especiales' => 'nullable|string',
            'necesita_hogar_temporal' => 'boolean',
            'apto_con_ninos' => 'boolean',
            'apto_con_otros_animales' => 'boolean',
            'vacunas' => 'nullable|array',
            'vacunas.*' => 'exists:tipos_vacunas,id',
            'fecha_ingreso' => 'sometimes|date',
            'fecha_salida' => 'nullable|date|after_or_equal:fecha_ingreso',
            'foto_principal' => 'nullable|image|max:2048',
            'galeria_fotos' => 'nullable|array',
            'galeria_fotos.*' => 'image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $mascota->update($validated);

            // Actualizar foto principal si se sube nueva
            if ($request->hasFile('foto_principal')) {
                if ($mascota->foto_principal) {
                    Storage::disk('public')->delete($mascota->foto_principal);
                }
                $path = $request->file('foto_principal')->store('mascotas', 'public');
                $mascota->update(['foto_principal' => $path]);
            }

            // Actualizar galería
            if ($request->hasFile('galeria_fotos')) {
                $galeriaActual = json_decode($mascota->galeria_fotos, true) ?? [];
                $nuevasFotos = [];
                foreach ($request->file('galeria_fotos') as $foto) {
                    $path = $foto->store('mascotas/galeria', 'public');
                    $nuevasFotos[] = $path;
                }
                $galeriaCompleta = array_merge($galeriaActual, $nuevasFotos);
                $mascota->update(['galeria_fotos' => json_encode($galeriaCompleta)]);
            }

            // Actualizar razas
            if (isset($validated['razas'])) {
                $mascota->razas()->sync($validated['razas']);
            }

            // Actualizar vacunas
            if (isset($validated['vacunas'])) {
                $vacunasConFecha = [];
                foreach ($validated['vacunas'] as $vacunaId) {
                    $vacunasConFecha[$vacunaId] = ['fecha_aplicacion' => now()];
                }
                $mascota->vacunas()->sync($vacunasConFecha);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mascota actualizada exitosamente',
                'data' => $mascota->load(['razas', 'vacunas'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar mascota: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una mascota (soft delete)
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $mascota = Mascota::findOrFail($id);

        if ($user->tipo === 'fundacion' && $mascota->fundacion_id !== $entidad->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $mascota->delete();

        return response()->json([
            'success' => true,
            'message' => 'Mascota eliminada exitosamente'
        ]);
    }

    /**
     * Eliminar una foto de la galería
     */
    public function eliminarFotoGaleria(Request $request, $id)
    {
        $user = auth()->user();
        $entidad = $this->getEntidad($user);

        if (!$entidad) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la entidad asociada'
            ], 404);
        }

        $validated = $request->validate([
            'foto_url' => 'required|string'
        ]);

        $mascota = Mascota::findOrFail($id);

        if ($user->tipo === 'fundacion' && $mascota->fundacion_id !== $entidad->id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $galeria = json_decode($mascota->galeria_fotos, true) ?? [];
        $galeria = array_filter($galeria, function($foto) use ($validated) {
            return $foto !== $validated['foto_url'];
        });

        // Eliminar archivo físico
        Storage::disk('public')->delete($validated['foto_url']);

        $mascota->update(['galeria_fotos' => json_encode(array_values($galeria))]);

        return response()->json([
            'success' => true,
            'message' => 'Foto eliminada exitosamente'
        ]);
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
