<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Mascota;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdopcionController extends Controller
{
    /**
     * Listado de mascotas disponibles para adopción
     */
    public function index(Request $request)
    {
        $mascotas = Mascota::with('fundacion')
            ->where('estado', 'En adopcion')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $mascotas
        ]);
    }

    /**
     * Mascotas disponibles (alias de index)
     */
    public function disponibles()
    {
        return $this->index(request());
    }

    /**
     * Detalle de mascota para adopción
     */
    public function show($id)
    {
        $mascota = Mascota::with(['fundacion', 'razas'])
            ->where('estado', 'En adopcion')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $mascota
        ]);
    }

    /**
     * Verificar disponibilidad de mascota
     */
    public function verificarDisponibilidad($id)
    {
        $mascota = Mascota::find($id);

        if (!$mascota) {
            return response()->json([
                'success' => false,
                'message' => 'Mascota no encontrada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'disponible' => $mascota->estado === 'En adopcion',
                'estado' => $mascota->estado,
                'nombre' => $mascota->nombre_mascota
            ]
        ]);
    }
}
