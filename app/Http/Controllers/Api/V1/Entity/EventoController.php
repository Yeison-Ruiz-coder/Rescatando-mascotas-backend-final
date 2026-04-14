<?php

namespace App\Http\Controllers\Api\V1\Entity;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::where('fundacion_id', auth()->user()->fundacion_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $eventos
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Nombre_evento' => 'required|string|max:255',
            'Lugar_evento' => 'required|string|max:255',
            'Descripcion' => 'required|string',
            'Fecha_evento' => 'required|date',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $evento = new Evento();
            $evento->nombre_evento = $request->Nombre_evento;
            $evento->lugar_evento = $request->Lugar_evento;
            $evento->descripcion = $request->Descripcion;
            $evento->fecha_evento = $request->Fecha_evento;
            $evento->fundacion_id = auth()->user()->fundacion_id ?? 1;
            $evento->tipo = 'fundacion';
            $evento->likes = 0;

            // ✅ MANEJO DE LA IMAGEN
            if ($request->hasFile('imagen')) {
                // Guardar la imagen en storage/app/public/eventos
                $path = $request->file('imagen')->store('eventos', 'public');
                $evento->imagen_url = '/storage/' . $path;
            }

            $evento->save();

            return response()->json([
                'success' => true,
                'message' => 'Evento creado exitosamente',
                'data' => $evento
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el evento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $evento = Evento::where('fundacion_id', auth()->user()->fundacion_id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $evento
        ]);
    }

    public function update(Request $request, $id)
    {
        $evento = Evento::where('fundacion_id', auth()->user()->fundacion_id)
            ->where('id', $id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'Nombre_evento' => 'sometimes|string|max:255',
            'Lugar_evento' => 'sometimes|string|max:255',
            'Descripcion' => 'sometimes|string',
            'Fecha_evento' => 'sometimes|date',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->has('Nombre_evento')) $evento->nombre_evento = $request->Nombre_evento;
        if ($request->has('Lugar_evento')) $evento->lugar_evento = $request->Lugar_evento;
        if ($request->has('Descripcion')) $evento->descripcion = $request->Descripcion;
        if ($request->has('Fecha_evento')) $evento->fecha_evento = $request->Fecha_evento;

        // ✅ ACTUALIZAR IMAGEN
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe
            if ($evento->imagen_url && file_exists(public_path($evento->imagen_url))) {
                unlink(public_path($evento->imagen_url));
            }
            $path = $request->file('imagen')->store('eventos', 'public');
            $evento->imagen_url = '/storage/' . $path;
        }

        $evento->save();

        return response()->json([
            'success' => true,
            'message' => 'Evento actualizado',
            'data' => $evento
        ]);
    }

    public function destroy($id)
    {
        $evento = Evento::where('fundacion_id', auth()->user()->fundacion_id)
            ->where('id', $id)
            ->firstOrFail();

        // ✅ ELIMINAR IMAGEN
        if ($evento->imagen_url && file_exists(public_path($evento->imagen_url))) {
            unlink(public_path($evento->imagen_url));
        }

        $evento->delete();

        return response()->json([
            'success' => true,
            'message' => 'Evento eliminado'
        ]);
    }
}
