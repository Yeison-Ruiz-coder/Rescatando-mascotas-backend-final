<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Mascota;
use Illuminate\Http\Request;

class MascotaController extends Controller
{
    /**
     * Listado de mascotas en adopción
     */
    public function index(Request $request)
    {
        $query = Mascota::with(['fundacion', 'razas'])
            ->where('estado', 'En adopcion');

        // Filtros
        if ($request->has('especie')) {
            $query->where('especie', $request->especie);
        }

        if ($request->has('fundacion_id')) {
            $query->where('fundacion_id', $request->fundacion_id);
        }

        if ($request->has('genero')) {
            $query->where('genero', $request->genero);
        }

        // Búsqueda
        if ($request->has('buscar')) {
            $query->where('nombre_mascota', 'like', '%' . $request->buscar . '%');
        }

        // Paginación
        $perPage = $request->get('per_page', 15);
        $mascotas = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $mascotas
        ]);
    }

    /**
     * Detalle de mascota
     */
    public function show($id)
    {
        $mascota = Mascota::with(['fundacion', 'razas', 'vacunas', 'historialMedico'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $mascota
        ]);
    }

    /**
     * Mascotas por especie
     */
    public function porEspecie($especie)
    {
        $mascotas = Mascota::with('fundacion')
            ->where('especie', $especie)
            ->where('estado', 'En adopcion')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $mascotas
        ]);
    }

    /**
     * Mascotas por fundación
     */
    public function porFundacion($fundacionId)
    {
        $mascotas = Mascota::with('fundacion')
            ->where('fundacion_id', $fundacionId)
            ->where('estado', 'En adopcion')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $mascotas
        ]);
    }
}
