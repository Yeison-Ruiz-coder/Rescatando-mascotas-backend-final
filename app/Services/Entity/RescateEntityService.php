<?php

namespace App\Services\Entity;

use App\Models\Rescate;
use App\Models\Mascota;
use App\Models\Notificacion;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RescateEntityService
{
    use ImageUploadTrait;

    public function getEntidad()
    {
        $user = Auth::user();

        if ($user->tipo === 'veterinaria') {
            return $user->veterinaria;
        }
        if ($user->tipo === 'fundacion') {
            return $user->fundacion;
        }
        return null;
    }

    public function findById(int $id): Rescate
    {
        return Rescate::with(['usuarioReporto', 'mascota', 'entidadResponsable'])
            ->findOrFail($id);
    }

    public function completarRescate(int $id)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->where('estado', 'en_proceso')
            ->findOrFail($id);

        $rescate->update(['estado' => 'completado']);

        return $rescate;
    }

    public function getRescatesDisponibles(Request $request)
    {
        $user = Auth::user();
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $lat = $entidad->lat ?? null;
        $lng = $entidad->lng ?? null;
        $radio = $entidad->radio_atencion ?? 10;

        $userTipo = $user->tipo;

        $rescates = Rescate::where('estado', 'pendiente')
            ->where(function ($query) use ($userTipo) {
                if ($userTipo === 'veterinaria') {
                    $query->whereIn('tipo_emergencia', ['herido', 'urgente']);
                } else {
                    $query->whereIn('tipo_emergencia', ['abandonado', 'urgente']);
                }
            });

        if ($lat && $lng) {
            $rescates = $rescates->selectRaw("*, (6371 * acos(cos(radians(?)) * cos(radians(lat)) * cos(radians(lng) - radians(?)) + sin(radians(?)) * sin(radians(lat)))) AS distance", [$lat, $lng, $lat])
                ->having('distance', '<', $radio)
                ->orderBy('distance');
        }

        return $rescates->orderBy('prioridad', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate(15);
    }

    public function aceptarRescate(int $id)
    {
        $user = Auth::user();
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('estado', 'pendiente')->findOrFail($id);

        $puedeAceptar = false;
        if ($user->tipo === 'veterinaria' && in_array($rescate->tipo_emergencia, ['herido', 'urgente'])) {
            $puedeAceptar = true;
        }
        if ($user->tipo === 'fundacion' && in_array($rescate->tipo_emergencia, ['abandonado', 'urgente'])) {
            $puedeAceptar = true;
        }

        if (!$puedeAceptar) {
            throw new \Exception('No puedes aceptar este tipo de rescate');
        }

        $rescate->update([
            'estado' => 'en_proceso',
            'entidad_responsable_type' => get_class($entidad),
            'entidad_responsable_id' => $entidad->id,
        ]);

        if ($rescate->usuario_reporto_id) {
            $nombreEntidad = $user->tipo === 'veterinaria'
                ? ($entidad->Nombre_vet ?? $entidad->nombre)
                : ($entidad->Nombre_1 ?? $entidad->nombre);

            Notificacion::create([
                'user_id' => $rescate->usuario_reporto_id,
                'contenido' => "Tu reporte de rescate fue aceptado por {$nombreEntidad}",
                'creado_por_id' => $user->id,
            ]);
        }

        return $rescate->load(['usuarioReporto', 'entidadResponsable']);
    }

    public function rechazarRescate(int $id)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('estado', 'pendiente')->findOrFail($id);

        $rescate->update([
            'estado' => 'pendiente'
        ]);

        return $rescate;
    }

    public function getMisRescates()
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        return Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->with(['usuarioReporto', 'mascota'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function registrarMascotaDesdeRescate(int $id, array $data, $files = null)
    {
        $user = Auth::user();
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('No se encontró la entidad asociada');
        }

        $rescate = Rescate::where('entidad_responsable_type', get_class($entidad))
            ->where('entidad_responsable_id', $entidad->id)
            ->where('estado', 'en_proceso')
            ->findOrFail($id);

        $mascota = Mascota::create([
            'nombre_mascota' => $data['nombre_mascota'],
            'especie' => $data['especie'],
            'edad_aprox' => $data['edad_aprox'] ?? null,
            'genero' => $data['genero'] ?? null,
            'descripcion' => $data['descripcion'] ?? null,
            'necesita_hogar_temporal' => $data['necesita_hogar_temporal'] ?? false,
            'apto_con_ninos' => $data['apto_con_ninos'] ?? true,
            'apto_con_otros_animales' => $data['apto_con_otros_animales'] ?? true,
            'condiciones_especiales' => $data['condiciones_especiales'] ?? null,
            'fecha_ingreso' => $data['fecha_ingreso'],
            'estado' => ($data['necesita_hogar_temporal'] ?? false) ? 'En acogida' : 'En adopcion',
            'fundacion_id' => $user->tipo === 'fundacion' ? $entidad->id : null,
            'lugar_rescate' => $rescate->lugar_rescate,
        ]);

        if (!empty($files['foto_principal'])) {
            $mascota->foto_principal = $this->uploadImage($files['foto_principal'], 'mascotas');
            $mascota->save();
        }

        $rescate->update([
            'mascota_id' => $mascota->id,
            'estado' => 'completado'
        ]);

        if ($rescate->usuario_reporto_id) {
            Notificacion::create([
                'user_id' => $rescate->usuario_reporto_id,
                'contenido' => "La mascota que reportaste ({$mascota->nombre_mascota}) ha sido registrada y está en proceso de adopción",
                'creado_por_id' => $user->id,
            ]);
        }

        return $mascota->load(['razas', 'vacunas']);
    }
}
