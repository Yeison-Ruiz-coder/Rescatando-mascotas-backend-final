<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Models\Donacion;
use App\Models\Fundacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DonacionController extends Controller
{
    /**
     * Listado de donaciones del usuario
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $donaciones = Donacion::with('fundacion')
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $donaciones
        ]);
    }

    /**
     * Crear una donación
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fundacion_id' => 'required|exists:fundaciones,id',
            'valor_donacion' => 'required|numeric|min:1000',
            'publica' => 'boolean',
            'metodo_pago' => 'required|in:nequi,bancolombia,pse,tarjeta',
            'comentario' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $donacion = Donacion::create([
                'user_id' => $request->user()->id,
                'fundacion_id' => $request->fundacion_id,
                'valor_donacion' => $request->valor_donacion,
                'publica' => $request->publica ?? false,
                'fecha_donacion' => now(),
                'comentario' => $request->comentario,
            ]);

            // Aquí iría la lógica de procesamiento de pago real
            // Por ahora simulamos éxito

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '¡Gracias por tu donación!',
                'data' => $donacion->load('fundacion')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la donación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detalle de donación
     */
    public function show($id)
    {
        $user = request()->user();

        $donacion = Donacion::with('fundacion')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $donacion
        ]);
    }

    /**
     * Obtener certificado de donación (PDF)
     */
    public function certificado($id)
    {
        $user = request()->user();

        $donacion = Donacion::with('fundacion')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        // Aquí generarías el PDF
        // Por ahora retornamos los datos

        return response()->json([
            'success' => true,
            'message' => 'Certificado generado',
            'data' => [
                'donacion' => $donacion,
                'certificado_url' => '/api/v1/user/donaciones/' . $id . '/certificado.pdf'
            ]
        ]);
    }
}
