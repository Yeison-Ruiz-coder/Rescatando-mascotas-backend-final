<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\Fundacion;
use Illuminate\Http\Request;

class FundacionController extends Controller
{
    /**
     * Display a listing of all fundaciones.
     */
    public function index()
    {
        // ✅ Seleccionar SOLO las columnas que existen en tu tabla
        $fundaciones = Fundacion::select(
                'id',
                'Nombre_1',
                'Direccion',
                'Telefono',
                'Email',
                'registro_sanitario',
                'capacidad_maxima',
                'horario_atencion',
                'recibe_voluntarios',
                'created_at'
            )
            ->orderBy('Nombre_1', 'asc')
            ->get();

        // Agregar valores por defecto para columnas que no existen
        foreach ($fundaciones as $fundacion) {
            $fundacion->total_mascotas = $fundacion->mascotas()->count();
            $fundacion->total_adopciones = $fundacion->adopciones()->count();
            $fundacion->imagen_portada = null;
            $fundacion->verificado = false;
            $fundacion->ciudad = null;
        }

        return response()->json([
            'success' => true,
            'data' => $fundaciones
        ]);
    }

    /**
     * Display the specified fundacion.
     */
    public function show($id)
    {
        $fundacion = Fundacion::with(['mascotas' => function($query) {
                $query->where('estado', 'En adopcion')
                      ->select('id', 'nombre_mascota', 'foto_principal', 'fundacion_id', 'especie', 'genero')
                      ->limit(6);
            }])
            ->select(
                'id',
                'Nombre_1',
                'Direccion',
                'Telefono',
                'Email',
                'registro_sanitario',
                'capacidad_maxima',
                'necesidades_actuales',
                'horario_atencion',
                'recibe_voluntarios',
                'user_id',
                'created_at',
                'updated_at'
            )
            ->findOrFail($id);

        // Decodificar necesidades_actuales si es JSON string
        if ($fundacion->necesidades_actuales && is_string($fundacion->necesidades_actuales)) {
            $fundacion->necesidades_actuales = json_decode($fundacion->necesidades_actuales, true);
        }

        // Valores por defecto para columnas que no existen
        $fundacion->imagen_portada = null;
        $fundacion->verificado = false;
        $fundacion->ciudad = null;
        $fundacion->fecha_fundacion = null;

        // Formatear mascotas para el frontend
        $fundacion->mascotas = $fundacion->mascotas->map(function($mascota) {
            return [
                'id' => $mascota->id,
                'nombre_mascota' => $mascota->nombre_mascota,
                'especie' => $mascota->especie,
                'genero' => $mascota->genero,
                'foto_principal' => $mascota->foto_principal ? '/storage/' . $mascota->foto_principal : null
            ];
        });

        // Agregar estadísticas adicionales
        $fundacion->total_mascotas = $fundacion->mascotas()->count();
        $fundacion->total_adopciones = $fundacion->adopciones()->count();
        $fundacion->total_donaciones = $fundacion->donaciones()->count();

        return response()->json([
            'success' => true,
            'data' => $fundacion
        ]);
    }

    /**
     * Get fundaciones by city.
     */
    public function porCiudad($ciudad)
    {
        // Como no hay columna 'ciudad', devolvemos array vacío o filtramos por Direccion
        $fundaciones = Fundacion::where('Direccion', 'like', "%{$ciudad}%")
            ->select(
                'id',
                'Nombre_1',
                'Direccion',
                'Telefono',
                'Email',
                'created_at'
            )
            ->orderBy('Nombre_1', 'asc')
            ->get();

        foreach ($fundaciones as $fundacion) {
            $fundacion->total_mascotas = $fundacion->mascotas()->count();
            $fundacion->imagen_portada = null;
            $fundacion->verificado = false;
            $fundacion->ciudad = $ciudad;
        }

        return response()->json([
            'success' => true,
            'data' => $fundaciones
        ]);
    }

    /**
     * Get fundaciones that accept volunteers.
     */
    public function recibenVoluntarios()
    {
        $fundaciones = Fundacion::where('recibe_voluntarios', true)
            ->select(
                'id',
                'Nombre_1',
                'Direccion',
                'Telefono',
                'Email',
                'horario_atencion',
                'created_at'
            )
            ->orderBy('Nombre_1', 'asc')
            ->get();

        foreach ($fundaciones as $fundacion) {
            $fundacion->total_mascotas = $fundacion->mascotas()->count();
            $fundacion->imagen_portada = null;
            $fundacion->verificado = false;
            $fundacion->ciudad = null;
        }

        return response()->json([
            'success' => true,
            'data' => $fundaciones
        ]);
    }

    /**
     * Get verified fundaciones only.
     */
    public function verificadas()
    {
        // Como no hay columna 'verificado', devolvemos todas o ninguna
        $fundaciones = collect(); // Array vacío por defecto

        return response()->json([
            'success' => true,
            'data' => $fundaciones
        ]);
    }

    /**
     * Get fundaciones statistics.
     */
    public function estadisticas()
    {
        $totalFundaciones = Fundacion::count();
        $fundacionesConVoluntarios = Fundacion::where('recibe_voluntarios', true)->count();

        $totalMascotas = \App\Models\Mascota::count();
        $totalAdopciones = \App\Models\Adopcion::count();
        $totalDonaciones = \App\Models\Donacion::sum('monto');

        return response()->json([
            'success' => true,
            'data' => [
                'total_fundaciones' => $totalFundaciones,
                'fundaciones_verificadas' => 0,
                'fundaciones_con_voluntarios' => $fundacionesConVoluntarios,
                'total_mascotas' => $totalMascotas,
                'total_adopciones' => $totalAdopciones,
                'total_donaciones' => $totalDonaciones,
                'ciudades_top' => []
            ]
        ]);
    }

    /**
     * Search fundaciones by name or description.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        $fundaciones = Fundacion::where('Nombre_1', 'like', "%{$query}%")
            ->orWhere('Direccion', 'like', "%{$query}%")
            ->select(
                'id',
                'Nombre_1',
                'Direccion',
                'Telefono',
                'Email',
                'created_at'
            )
            ->orderBy('Nombre_1', 'asc')
            ->get();

        foreach ($fundaciones as $fundacion) {
            $fundacion->total_mascotas = $fundacion->mascotas()->count();
            $fundacion->imagen_portada = null;
            $fundacion->verificado = false;
            $fundacion->ciudad = null;
        }

        return response()->json([
            'success' => true,
            'data' => $fundaciones
        ]);
    }
}
