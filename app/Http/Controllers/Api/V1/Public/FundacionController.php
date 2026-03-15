<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Fundacion;
use Illuminate\Http\Request;

class FundacionController extends Controller
{
    /**
     * Listado de fundaciones
     */
    public function index(Request $request)
    {
        $query = Fundacion::withCount('mascotas');

        if ($request->has('recibe_voluntarios')) {
            $query->where('recibe_voluntarios', true);
        }

        if ($request->has('buscar')) {
            $query->where('Nombre_1', 'like', '%' . $request->buscar . '%');
        }

        $fundaciones = $query->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $fundaciones
        ]);
    }

    /**
     * Detalle de fundación
     */
    public function show($id)
    {
        $fundacion = Fundacion::with('mascotas')
            ->withCount('mascotas')
            ->findOrFail($id);

        $necesidades = json_decode($fundacion->necesidades_actuales, true) ?? [];

        return response()->json([
            'success' => true,
            'data' => [
                'fundacion' => $fundacion,
                'necesidades' => $necesidades,
                'mascotas' => $fundacion->mascotas()->where('estado', 'En adopcion')->get()
            ]
        ]);
    }
}
