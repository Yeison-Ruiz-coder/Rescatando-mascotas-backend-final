<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mascota;
use App\Models\Raza;
use App\Models\TipoVacuna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MascotaController extends Controller
{
    /**
     * Listado de mascotas con filtros
     */
    public function index(Request $request)
    {
        $query = Mascota::with(['fundacion', 'razas', 'vacunas']);

        // Filtros
        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->has('especie')) {
            $query->where('especie', $request->especie);
        }

        if ($request->has('fundacion_id')) {
            $query->where('fundacion_id', $request->fundacion_id);
        }

        if ($request->has('buscar')) {
            $query->where('nombre_mascota', 'like', '%' . $request->buscar . '%');
        }

        $mascotas = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $mascotas
        ]);
    }

    /**
     * Crear nueva mascota
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_mascota' => 'required|string|max:255',
            'especie' => 'required|string',
            'edad_aprox' => 'nullable|integer|min:0|max:30',
            'genero' => 'required|in:Macho,Hembra,Desconocido',
            'estado' => 'required|in:En adopcion,Adoptado,Rescatada,En acogida',
            'lugar_rescate' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'foto_principal' => 'nullable|image|max:2048',
            'galeria_fotos.*' => 'nullable|image|max:2048',
            'fundacion_id' => 'required|exists:fundaciones,id',
            'necesita_hogar_temporal' => 'boolean',
            'apto_con_ninos' => 'boolean',
            'apto_con_otros_animales' => 'boolean',
            'condiciones_especiales' => 'nullable|string',
            'fecha_ingreso' => 'nullable|date',
            'razas' => 'nullable|array',
            'razas.*' => 'exists:razas,id',
            'vacunas' => 'nullable|array',
            'vacunas.*' => 'exists:tipos_vacunas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $data = $request->except(['foto_principal', 'galeria_fotos', 'razas', 'vacunas']);

            // Booleanos
            $data['necesita_hogar_temporal'] = $request->boolean('necesita_hogar_temporal');
            $data['apto_con_ninos'] = $request->boolean('apto_con_ninos');
            $data['apto_con_otros_animales'] = $request->boolean('apto_con_otros_animales');

            // Foto principal
            if ($request->hasFile('foto_principal')) {
                $path = $request->file('foto_principal')->store('mascotas', 'public');
                $data['foto_principal'] = $path;
            }

            // Galería
            if ($request->hasFile('galeria_fotos')) {
                $galeria = [];
                foreach ($request->file('galeria_fotos') as $foto) {
                    $galeria[] = $foto->store('mascotas/galeria', 'public');
                }
                $data['galeria_fotos'] = $galeria;
            }

            $mascota = Mascota::create($data);

            // Sincronizar razas
            if ($request->has('razas')) {
                $mascota->razas()->sync($request->razas);
            }

            // Sincronizar vacunas
            if ($request->has('vacunas')) {
                $vacunasData = [];
                foreach ($request->vacunas as $vacunaId) {
                    $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()];
                }
                $mascota->vacunas()->sync($vacunasData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mascota creada exitosamente',
                'data' => $mascota->load(['fundacion', 'razas', 'vacunas'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear mascota',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar mascota
     */
    public function show($id)
    {
        $mascota = Mascota::with(['fundacion', 'razas', 'vacunas', 'historialMedico', 'adopciones', 'rescates'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $mascota
        ]);
    }

    /**
     * Actualizar mascota
     */
    public function update(Request $request, $id)
    {
        $mascota = Mascota::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'nombre_mascota' => 'sometimes|string|max:255',
            'especie' => 'sometimes|string',
            'edad_aprox' => 'nullable|integer|min:0|max:30',
            'genero' => 'sometimes|in:Macho,Hembra,Desconocido',
            'estado' => 'sometimes|in:En adopcion,Adoptado,Rescatada,En acogida',
            'lugar_rescate' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'foto_principal' => 'nullable|image|max:2048',
            'galeria_fotos.*' => 'nullable|image|max:2048',
            'fundacion_id' => 'sometimes|exists:fundaciones,id',
            'necesita_hogar_temporal' => 'boolean',
            'apto_con_ninos' => 'boolean',
            'apto_con_otros_animales' => 'boolean',
            'condiciones_especiales' => 'nullable|string',
            'fecha_ingreso' => 'nullable|date',
            'razas' => 'nullable|array',
            'razas.*' => 'exists:razas,id',
            'vacunas' => 'nullable|array',
            'vacunas.*' => 'exists:tipos_vacunas,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $data = $request->except(['foto_principal', 'galeria_fotos', 'razas', 'vacunas']);

            // Booleanos
            if ($request->has('necesita_hogar_temporal')) {
                $data['necesita_hogar_temporal'] = $request->boolean('necesita_hogar_temporal');
            }
            if ($request->has('apto_con_ninos')) {
                $data['apto_con_ninos'] = $request->boolean('apto_con_ninos');
            }
            if ($request->has('apto_con_otros_animales')) {
                $data['apto_con_otros_animales'] = $request->boolean('apto_con_otros_animales');
            }

            // Foto principal
            if ($request->hasFile('foto_principal')) {
                if ($mascota->foto_principal) {
                    Storage::disk('public')->delete($mascota->foto_principal);
                }
                $path = $request->file('foto_principal')->store('mascotas', 'public');
                $data['foto_principal'] = $path;
            }

            // Galería
            if ($request->hasFile('galeria_fotos')) {
                $galeria = $mascota->galeria_fotos ?? [];
                foreach ($request->file('galeria_fotos') as $foto) {
                    $galeria[] = $foto->store('mascotas/galeria', 'public');
                }
                $data['galeria_fotos'] = $galeria;
            }

            $mascota->update($data);

            // Sincronizar razas
            if ($request->has('razas')) {
                $mascota->razas()->sync($request->razas);
            }

            // Sincronizar vacunas
            if ($request->has('vacunas')) {
                $vacunasData = [];
                foreach ($request->vacunas as $vacunaId) {
                    $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()];
                }
                $mascota->vacunas()->sync($vacunasData);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mascota actualizada exitosamente',
                'data' => $mascota->fresh(['fundacion', 'razas', 'vacunas'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar mascota',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar mascota
     */
    public function destroy($id)
    {
        $mascota = Mascota::findOrFail($id);

        DB::beginTransaction();

        try {
            // Eliminar fotos
            if ($mascota->foto_principal) {
                Storage::disk('public')->delete($mascota->foto_principal);
            }

            if ($mascota->galeria_fotos) {
                foreach ($mascota->galeria_fotos as $foto) {
                    Storage::disk('public')->delete($foto);
                }
            }

            // Eliminar relaciones
            $mascota->razas()->detach();
            $mascota->vacunas()->detach();

            $mascota->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Mascota eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar mascota',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar foto de galería
     */
    public function eliminarFotoGaleria(Request $request, $id)
    {
        $request->validate([
            'foto' => 'required|string'
        ]);

        $mascota = Mascota::findOrFail($id);
        $galeria = $mascota->galeria_fotos ?? [];

        if (in_array($request->foto, $galeria)) {
            Storage::disk('public')->delete($request->foto);
            $galeria = array_values(array_diff($galeria, [$request->foto]));
            $mascota->update(['galeria_fotos' => $galeria]);

            return response()->json([
                'success' => true,
                'message' => 'Foto eliminada'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Foto no encontrada'
        ], 404);
    }
}
