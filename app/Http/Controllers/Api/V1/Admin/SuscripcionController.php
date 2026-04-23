<?php

namespace App\Http\Controllers;

use App\Models\Suscripcion;
use Illuminate\Http\Request;

class SuscripcionController extends Controller
{
    // 📌 LISTAR
    public function index()
    {
        return Suscripcion::with(['user', 'mascota'])->get();
    }

    // 📌 CREAR
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'mascota_id' => 'required|exists:mascotas,id',
            'monto_mensual' => 'required|numeric|min:1',

            // 🔥 AJUSTADO A TU MIGRACIÓN
            'frecuencia' => 'required|in:unica,mensual,trimestral,anual',

            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',

            'mensaje_apoyo' => 'nullable|string',

            // 🔥 AJUSTADO A TU MIGRACIÓN
            'estado' => 'required|in:activo,pausado,cancelado,finalizado',
        ]);

        $suscripcion = Suscripcion::create($validated);

        return response()->json([
            'message' => 'Suscripción creada correctamente',
            'data' => $suscripcion
        ], 201);
    }

    // 📌 MOSTRAR
    public function show($id)
    {
        return Suscripcion::with(['user', 'mascota'])->findOrFail($id);
    }

    // 📌 ACTUALIZAR
    public function update(Request $request, $id)
    {
        $suscripcion = Suscripcion::findOrFail($id);

        $suscripcion->update($request->all());

        return response()->json([
            'message' => 'Suscripción actualizada',
            'data' => $suscripcion
        ]);
    }

    // 📌 ELIMINAR
    public function destroy($id)
    {
        Suscripcion::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Suscripción eliminada'
        ]);
    }
}