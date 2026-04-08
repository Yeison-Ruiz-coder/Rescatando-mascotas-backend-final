<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Models\Mascota;
use App\Models\Fundacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MascotaController extends Controller
{
    /**
     * Obtener todas las mascotas de la fundación autenticada
     */
    public function index()
    {
        $user = Auth::user();

        // Verificar que el usuario sea tipo fundacion (sin importar el estado)
        if ($user->tipo !== 'fundacion') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos'
            ], 403);
        }

        // Buscar la fundación (incluyendo usuarios pendientes)
        $fundacion = Fundacion::where('user_id', $user->id)->first();

        // Si no existe, crearla automáticamente
        if (!$fundacion) {
            $fundacion = Fundacion::create([
                'Nombre_1' => $user->nombre ?? 'Fundación',
                'Direccion' => $user->direccion ?? 'Pendiente',
                'Telefono' => $user->telefono ?? '000000000',
                'Email' => $user->email,
                'registro_sanitario' => 'PENDIENTE_' . $user->id,
                'user_id' => $user->id,
            ]);
        }

        $mascotas = Mascota::where('fundacion_id', $fundacion->id)->get();

        return response()->json([
            'success' => true,
            'data' => $mascotas
        ]);
    }

    /**
     * Crear una nueva mascota
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();

            // Verificar que el usuario sea tipo fundacion
            if ($user->tipo !== 'fundacion') {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para registrar mascotas'
                ], 403);
            }

            // Buscar el perfil de la fundación
            $fundacion = Fundacion::where('user_id', $user->id)->first();

            if (!$fundacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró perfil de fundación. Por favor, completa tu registro como fundación.'
                ], 404);
            }

            // Validar datos
            $validated = $request->validate([
                'nombre_mascota' => 'required|string|max:255',
                'especie' => 'required|string',
                'edad_aprox' => 'required|numeric',
                'genero' => 'required|string',
                'descripcion' => 'required|string',
                'estado' => 'required|string',
                'lugar_rescate' => 'nullable|string',
                'condiciones_especiales' => 'nullable|string',
                'necesita_hogar_temporal' => 'boolean',
                'apto_con_ninos' => 'boolean',
                'apto_con_otros_animales' => 'boolean',
                'foto_principal' => 'nullable|image|max:2048',
                'razas' => 'array',
                'vacunas' => 'array',
                'galeria_fotos' => 'array',
                'galeria_fotos.*' => 'image|max:2048'
            ]);

            // Crear la mascota
            $mascota = new Mascota();
            $mascota->fundacion_id = $fundacion->id;  // Usar el ID de la fundación
            $mascota->nombre_mascota = $request->nombre_mascota;
            $mascota->especie = $request->especie;
            $mascota->edad_aprox = $request->edad_aprox;
            $mascota->genero = $request->genero;
            $mascota->estado = $request->estado;
            $mascota->lugar_rescate = $request->lugar_rescate;
            $mascota->descripcion = $request->descripcion;
            $mascota->condiciones_especiales = $request->condiciones_especiales;
            $mascota->necesita_hogar_temporal = $request->necesita_hogar_temporal ?? false;
            $mascota->apto_con_ninos = $request->apto_con_ninos ?? true;
            $mascota->apto_con_otros_animales = $request->apto_con_otros_animales ?? true;
            $mascota->fecha_ingreso = $request->fecha_ingreso ?? now();

            // Guardar foto principal si existe
            if ($request->hasFile('foto_principal')) {
                $path = $request->file('foto_principal')->store('mascotas', 'public');
                $mascota->foto_principal = $path;
            }

            $mascota->save();

            // Guardar relaciones (razas, vacunas)
            if ($request->has('razas')) {
                $mascota->razas()->sync($request->razas);
            }

            if ($request->has('vacunas') && !empty($request->vacunas)) {
                $vacunasData = [];
                foreach ($request->vacunas as $vacunaId) {
                    $vacunasData[$vacunaId] = [
                        'fecha_aplicacion' => now()->format('Y-m-d'), // Fecha actual
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                $mascota->vacunas()->sync($vacunasData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Mascota registrada exitosamente',
                'data' => $mascota->load(['razas', 'vacunas'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar mascota: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una mascota específica
     */
    public function show($id)
    {
        try {
            $user = Auth::user();

            $fundacion = Fundacion::where('user_id', $user->id)->first();

            if (!$fundacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil de fundación no encontrado'
                ], 404);
            }

            $mascota = Mascota::where('fundacion_id', $fundacion->id)
                ->with(['razas', 'vacunas'])
                ->find($id);

            if (!$mascota) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mascota no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $mascota
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar una mascota
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();

            $fundacion = Fundacion::where('user_id', $user->id)->first();

            if (!$fundacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil de fundación no encontrado'
                ], 404);
            }

            $mascota = Mascota::where('fundacion_id', $fundacion->id)->find($id);

            if (!$mascota) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mascota no encontrada'
                ], 404);
            }

            // Actualizar campos
            $mascota->update($request->only([
                'nombre_mascota',
                'especie',
                'edad_aprox',
                'genero',
                'estado',
                'lugar_rescate',
                'descripcion',
                'condiciones_especiales',
                'necesita_hogar_temporal',
                'apto_con_ninos',
                'apto_con_otros_animales'
            ]));

            // Actualizar foto principal si se sube una nueva
            if ($request->hasFile('foto_principal')) {
                // Eliminar foto anterior
                if ($mascota->foto_principal) {
                    Storage::disk('public')->delete($mascota->foto_principal);
                }
                $path = $request->file('foto_principal')->store('mascotas', 'public');
                $mascota->foto_principal = $path;
                $mascota->save();
            }

            // Actualizar relaciones
            if ($request->has('razas')) {
                $mascota->razas()->sync($request->razas);
            }

            if ($request->has('vacunas')) {
                $vacunasData = [];
                foreach ($request->vacunas as $vacunaId) {
                    $vacunasData[$vacunaId] = [
                        'fecha_aplicacion' => now()->format('Y-m-d'), // Fecha actual
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
                $mascota->vacunas()->sync($vacunasData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Mascota actualizada exitosamente',
                'data' => $mascota->load(['razas', 'vacunas'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una mascota
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();

            $fundacion = Fundacion::where('user_id', $user->id)->first();

            if (!$fundacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Perfil de fundación no encontrado'
                ], 404);
            }

            $mascota = Mascota::where('fundacion_id', $fundacion->id)->find($id);

            if (!$mascota) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mascota no encontrada'
                ], 404);
            }

            // Eliminar fotos
            if ($mascota->foto_principal) {
                Storage::disk('public')->delete($mascota->foto_principal);
            }

            $mascota->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mascota eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
