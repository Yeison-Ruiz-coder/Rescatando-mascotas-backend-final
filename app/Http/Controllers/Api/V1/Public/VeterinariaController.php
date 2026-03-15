<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Veterinaria;
use Illuminate\Http\Request;

class VeterinariaController extends Controller
{
    /**
     * Listado de veterinarias
     */
    public function index(Request $request)
    {
        $query = Veterinaria::query();

        if ($request->has('urgencias')) {
            $query->where('urgencias_24h', true);
        }

        if ($request->has('ubicacion')) {
            $query->where('Direccion', 'like', '%' . $request->ubicacion . '%');
        }

        $veterinarias = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $veterinarias
        ]);
    }

    /**
     * Detalle de veterinaria
     */
    public function show($id)
    {
        $veterinaria = Veterinaria::findOrFail($id);
        $servicios = json_decode($veterinaria->servicios, true) ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'veterinaria' => $veterinaria,
                'servicios' => $servicios
            ]
        ]);
    }

    /**
     * Mapa de veterinarias con urgencias 24h
     */
    public function mapa()
    {
        $veterinarias = Veterinaria::where('urgencias_24h', true)
            ->get(['id', 'Nombre_vet', 'Direccion', 'Telefono', 'urgencias_24h']);

        return response()->json([
            'success' => true,
            'data' => $veterinarias
        ]);
    }
}
