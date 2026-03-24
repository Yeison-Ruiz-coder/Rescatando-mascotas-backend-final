<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Donacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DonacionController extends Controller
{
    /**
     * GET /api/donaciones
     * Listado de donaciones públicas
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'nullable|integer|min:1|max:100',
            'fundacion_id' => 'nullable|exists:fundaciones,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $query = Donacion::with(['usuario', 'fundacion'])
            ->where('publica', true);

        if ($request->filled('fundacion_id')) {
            $query->where('fundacion_id', $request->fundacion_id);
        }

        $donaciones = $query->latest()->paginate($request->get('per_page', 15));

        $totales = [
            'total' => Donacion::where('publica', true)->sum('valor_donacion'),
            'total_donantes' => Donacion::where('publica', true)->distinct('user_id')->count('user_id'),
            'promedio' => round(Donacion::where('publica', true)->avg('valor_donacion') ?? 0, 2),
        ];

        return response()->json([
            'success' => true,
            'data' => $donaciones,
            'totales' => $totales
        ]);
    }

    /**
     * GET /api/donaciones/{id}
     * Detalle de donación pública
     */
    public function show($id)
    {
        $donacion = Donacion::with(['usuario', 'fundacion'])
            ->where('publica', true)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $donacion
        ]);
    }
}
