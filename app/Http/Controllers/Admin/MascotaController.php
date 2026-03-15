<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mascota;
use App\Models\Raza;
use App\Models\Fundacion;
use App\Models\TipoVacuna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MascotaController extends Controller
{
    /**
     * Listado de mascotas (admin)
     */
    public function index(Request $request)
    {
        $query = Mascota::with(['fundacion', 'razas', 'vacunas']); // ✅ Agregué 'vacunas'

        // Filtros
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('especie')) {
            $query->where('especie', $request->especie);
        }

        if ($request->filled('fundacion_id')) {
            $query->where('fundacion_id', $request->fundacion_id);
        }

        if ($request->filled('buscar')) {
            $query->where(function($q) use ($request) {
                $q->where('nombre_mascota', 'like', '%' . $request->buscar . '%')
                  ->orWhere('lugar_rescate', 'like', '%' . $request->buscar . '%'); // ✅ Búsqueda mejorada
            });
        }

        $mascotas = $query->latest()->paginate(15);

        $estados = ['En adopcion', 'Adoptado', 'Rescatada', 'En acogida'];
        $especies = Mascota::whereNotNull('especie')->distinct('especie')->pluck('especie');

        return view('admin.mascotas.index', compact('mascotas', 'estados', 'especies'));
    }

    /**
     * Formulario de creación
     */
    public function create()
    {
        $fundaciones = Fundacion::all();
        $razas = Raza::orderBy('especie')->orderBy('nombre_raza')->get(); // ✅ Ordenado
        $vacunas = TipoVacuna::orderBy('nombre_vacuna')->get();

        return view('admin.mascotas.create', compact('fundaciones', 'razas', 'vacunas'));
    }

    /**
     * Guardar mascota
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre_mascota' => 'required|string|max:255',
            'especie' => 'required|string',
            'edad_aprox' => 'nullable|integer|min:0|max:30',
            'genero' => 'required|in:Macho,Hembra,Desconocido',
            'estado' => 'required|in:En adopcion,Adoptado,Rescatada,En acogida',
            'lugar_rescate' => 'nullable|string|max:255', // ✅ Agregado
            'descripcion' => 'nullable|string',
            'foto_principal' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'galeria_fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'fundacion_id' => 'required|exists:fundaciones,id',
            'necesita_hogar_temporal' => 'nullable|boolean', // ✅ Agregado
            'apto_con_ninos' => 'nullable|boolean',
            'apto_con_otros_animales' => 'nullable|boolean',
            'condiciones_especiales' => 'nullable|string',
            'fecha_ingreso' => 'nullable|date', // ✅ Agregado
            'fecha_salida' => 'nullable|date|after_or_equal:fecha_ingreso', // ✅ Agregado
            'razas' => 'nullable|array',
            'razas.*' => 'exists:razas,id',
            'vacunas' => 'nullable|array',
            'vacunas.*' => 'exists:tipos_vacunas,id',
        ]);

        DB::beginTransaction();

        try {
            // Preparar datos
            $data = $request->except(['foto_principal', 'galeria_fotos', 'razas', 'vacunas']);

            // Manejar booleanos (si no vienen en el request, serán false)
            $data['necesita_hogar_temporal'] = $request->has('necesita_hogar_temporal');
            $data['apto_con_ninos'] = $request->has('apto_con_ninos');
            $data['apto_con_otros_animales'] = $request->has('apto_con_otros_animales');

            // Foto principal
            if ($request->hasFile('foto_principal')) {
                $path = $request->file('foto_principal')->store('mascotas', 'public');
                $data['foto_principal'] = $path;
            }

            // Galería - ✅ CORREGIDO: Usar array directamente
            if ($request->hasFile('galeria_fotos')) {
                $galeria = [];
                foreach ($request->file('galeria_fotos') as $foto) {
                    $galeria[] = $foto->store('mascotas/galeria', 'public');
                }
                $data['galeria_fotos'] = $galeria; // El casts 'array' lo maneja
            }

            // Crear mascota
            $mascota = Mascota::create($data);

            // Sincronizar razas
            if ($request->has('razas')) {
                $mascota->razas()->sync($request->razas);
            }

            // Sincronizar vacunas con fecha actual
            if ($request->has('vacunas')) {
                $vacunasData = [];
                foreach ($request->vacunas as $vacunaId) {
                    $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()];
                }
                $mascota->vacunas()->sync($vacunasData);
            }

            DB::commit();

            return redirect()->route('admin.mascotas.index')
                ->with('success', 'Mascota creada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear mascota: ' . $e->getMessage());

            return back()->with('error', 'Error al crear mascota. Por favor, intenta nuevamente.')
                ->withInput();
        }
    }

    /**
     * Mostrar mascota
     */
    public function show($id)
    {
        $mascota = Mascota::with([
            'fundacion',
            'razas',
            'vacunas' => function($q) {
                $q->withPivot('fecha_aplicacion'); // ✅ Asegurar que traiga fecha_aplicacion
            },
            'historialMedico',
            'adopciones.solicitante',
            'rescates'
        ])->findOrFail($id);

        return view('admin.mascotas.show', compact('mascota'));
    }

    /**
     * Formulario de edición
     */
    public function edit($id)
    {
        $mascota = Mascota::with(['razas', 'vacunas'])->findOrFail($id);
        $fundaciones = Fundacion::all();
        $razas = Raza::orderBy('especie')->orderBy('nombre_raza')->get();
        $vacunas = TipoVacuna::orderBy('nombre_vacuna')->get();

        // Obtener IDs seleccionados
        $razasSeleccionadas = $mascota->razas->pluck('id')->toArray();
        $vacunasSeleccionadas = $mascota->vacunas->pluck('id')->toArray();

        return view('admin.mascotas.edit', compact(
            'mascota',
            'fundaciones',
            'razas',
            'vacunas',
            'razasSeleccionadas',
            'vacunasSeleccionadas'
        ));
    }

    /**
     * Actualizar mascota
     */
    public function update(Request $request, $id)
    {
        $mascota = Mascota::findOrFail($id);

        $request->validate([
            'nombre_mascota' => 'required|string|max:255',
            'especie' => 'required|string',
            'edad_aprox' => 'nullable|integer|min:0|max:30',
            'genero' => 'required|in:Macho,Hembra,Desconocido',
            'estado' => 'required|in:En adopcion,Adoptado,Rescatada,En acogida',
            'lugar_rescate' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'foto_principal' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'galeria_fotos.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'fundacion_id' => 'required|exists:fundaciones,id',
            'necesita_hogar_temporal' => 'nullable|boolean',
            'apto_con_ninos' => 'nullable|boolean',
            'apto_con_otros_animales' => 'nullable|boolean',
            'condiciones_especiales' => 'nullable|string',
            'fecha_ingreso' => 'nullable|date',
            'fecha_salida' => 'nullable|date|after_or_equal:fecha_ingreso',
            'razas' => 'nullable|array',
            'razas.*' => 'exists:razas,id',
            'vacunas' => 'nullable|array',
            'vacunas.*' => 'exists:tipos_vacunas,id',
        ]);

        DB::beginTransaction();

        try {
            $data = $request->except(['foto_principal', 'galeria_fotos', 'razas', 'vacunas']);

            // Manejar booleanos
            $data['necesita_hogar_temporal'] = $request->has('necesita_hogar_temporal');
            $data['apto_con_ninos'] = $request->has('apto_con_ninos');
            $data['apto_con_otros_animales'] = $request->has('apto_con_otros_animales');

            // Procesar foto principal
            if ($request->hasFile('foto_principal')) {
                // Eliminar foto anterior si existe
                if ($mascota->foto_principal) {
                    Storage::disk('public')->delete($mascota->foto_principal);
                }
                $path = $request->file('foto_principal')->store('mascotas', 'public');
                $data['foto_principal'] = $path;
            }

            // Procesar galería - ✅ VERSIÓN MEJORADA
            if ($request->hasFile('galeria_fotos')) {
                $galeria = $mascota->galeria_fotos ?? [];

                // Asegurar que sea array
                if (!is_array($galeria)) {
                    $galeria = [];
                }

                // Agregar nuevas fotos
                foreach ($request->file('galeria_fotos') as $foto) {
                    $galeria[] = $foto->store('mascotas/galeria', 'public');
                }

                $data['galeria_fotos'] = $galeria;
            }

            // Actualizar mascota
            $mascota->update($data);

            // Sincronizar razas
            if ($request->has('razas')) {
                $mascota->razas()->sync($request->razas);
            } else {
                $mascota->razas()->sync([]); // Limpiar si no se enviaron
            }

            // Sincronizar vacunas
            if ($request->has('vacunas')) {
                $vacunasData = [];
                foreach ($request->vacunas as $vacunaId) {
                    // Verificar si ya tenía fecha_aplicacion
                    $fechaExistente = $mascota->vacunas()
                        ->where('tipos_vacunas_id', $vacunaId)
                        ->first()?->pivot->fecha_aplicacion;

                    $vacunasData[$vacunaId] = [
                        'fecha_aplicacion' => $fechaExistente ?? now()
                    ];
                }
                $mascota->vacunas()->sync($vacunasData);
            } else {
                $mascota->vacunas()->sync([]);
            }

            DB::commit();

            return redirect()->route('admin.mascotas.index')
                ->with('success', 'Mascota actualizada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar mascota: ' . $e->getMessage());

            return back()->with('error', 'Error al actualizar. Por favor, intenta nuevamente.')
                ->withInput();
        }
    }

    /**
     * Eliminar mascota
     */
    public function destroy(Mascota $mascota)
    {
        DB::beginTransaction();

        try {
            // Eliminar foto principal
            if ($mascota->foto_principal) {
                Storage::disk('public')->delete($mascota->foto_principal);
            }

            // Eliminar fotos de galería
            if ($mascota->galeria_fotos && is_array($mascota->galeria_fotos)) {
                foreach ($mascota->galeria_fotos as $foto) {
                    if (!empty($foto)) {
                        Storage::disk('public')->delete($foto);
                    }
                }
            }

            // Eliminar relaciones (por las foreign keys con cascade no es necesario, pero por seguridad)
            $mascota->razas()->detach();
            $mascota->vacunas()->detach();

            // Eliminar la mascota
            $mascota->delete();

            DB::commit();

            return redirect()->route('admin.mascotas.index')
                ->with('success', 'Mascota eliminada exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar mascota: ' . $e->getMessage());

            return back()->with('error', 'Error al eliminar mascota.');
        }
    }

    /**
     * Eliminar una foto específica de la galería (AJAX)
     */
    public function eliminarFotoGaleria(Request $request, $id)
    {
        $request->validate([
            'foto' => 'required|string'
        ]);

        $mascota = Mascota::findOrFail($id);

        // Obtener galería actual
        $galeria = $mascota->galeria_fotos ?? [];

        if (is_array($galeria) && in_array($request->foto, $galeria)) {
            // Eliminar archivo físico
            Storage::disk('public')->delete($request->foto);

            // Quitar del array
            $galeria = array_values(array_diff($galeria, [$request->foto]));

            // Actualizar
            $mascota->update(['galeria_fotos' => $galeria]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }
}
