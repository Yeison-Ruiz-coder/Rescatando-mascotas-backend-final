<?php

namespace App\Services\Entity;

use App\Models\Mascota;
use App\Models\Fundacion;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class MascotaEntityService
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

    public function getFundacion()
    {
        $user = Auth::user();

        if ($user->tipo !== 'fundacion') {
            return null;
        }

        $fundacion = Fundacion::where('user_id', $user->id)->first();

        if (!$fundacion) {
            $fundacion = Fundacion::create([
                'Nombre_1' => $user->nombre ?? 'Fundación',
                'Direccion' => $user->direccion ?? 'Pendiente',
                'Telefono' => $user->telefono ?? '000000000',
                'Email' => $user->email,
                'registro_sanitario' => 'PENDIENTE_' . $user->id,
                'user_id' => $user->id,
                'ciudad' => $user->ciudad ?? null,
            ]);
        }

        return $fundacion;
    }

    public function getAllMascotas()
    {
        $fundacion = $this->getFundacion();

        if (!$fundacion) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        return Mascota::where('fundacion_id', $fundacion->id)->get();
    }

    public function findMascota(int $id)
    {
        $fundacion = $this->getFundacion();

        if (!$fundacion) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        $mascota = Mascota::where('fundacion_id', $fundacion->id)
            ->with(['razas', 'vacunas'])
            ->find($id);

        if (!$mascota) {
            throw new ModelNotFoundException('Mascota no encontrada');
        }

        return $mascota;
    }

    public function createMascota(array $data, $files = null)
    {
        $fundacion = $this->getFundacion();

        if (!$fundacion) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        $mascotaData = [
            'fundacion_id' => $fundacion->id,
            'nombre_mascota' => $data['nombre_mascota'],
            'especie' => $data['especie'],
            'edad_aprox' => $data['edad_aprox'],
            'genero' => $data['genero'],
            'estado' => $data['estado'],
            'lugar_rescate' => $data['lugar_rescate'] ?? null,
            'descripcion' => $data['descripcion'],
            'condiciones_especiales' => $data['condiciones_especiales'] ?? null,
            'necesita_hogar_temporal' => $data['necesita_hogar_temporal'] ?? false,
            'apto_con_ninos' => $data['apto_con_ninos'] ?? true,
            'apto_con_otros_animales' => $data['apto_con_otros_animales'] ?? true,
            'fecha_ingreso' => $data['fecha_ingreso'] ?? now(),
        ];

        // Nuevos campos
        if (isset($data['peso_aprox'])) {
            $mascotaData['peso_aprox'] = $data['peso_aprox'];
        }
        if (isset($data['tamano'])) {
            $mascotaData['tamano'] = $data['tamano'];
        }
        if (isset($data['color'])) {
            $mascotaData['color'] = $data['color'];
        }
        if (isset($data['salud_general'])) {
            $mascotaData['salud_general'] = $data['salud_general'];
        }
        if (isset($data['esterilizado'])) {
            $mascotaData['esterilizado'] = $data['esterilizado'];
        }
        if (isset($data['desparasitado'])) {
            $mascotaData['desparasitado'] = $data['desparasitado'];
        }
        if (isset($data['vacunado'])) {
            $mascotaData['vacunado'] = $data['vacunado'];
        }
        if (isset($data['video_url'])) {
            $mascotaData['video_url'] = $data['video_url'];
        }

        $mascota = new Mascota();
        $mascota->fill($mascotaData);

        // Guardar foto principal
        if (!empty($files['foto_principal']) && $files['foto_principal']->isValid()) {
            $mascota->foto_principal = $this->uploadImage($files['foto_principal'], 'mascotas');
        }

        $mascota->save();

        // Guardar galería de fotos
        if (!empty($files['galeria_fotos']) && is_array($files['galeria_fotos'])) {
            $galeriaPaths = [];
            foreach ($files['galeria_fotos'] as $foto) {
                if ($foto && $foto->isValid()) {
                    $path = $this->uploadImage($foto, 'mascotas/galeria');
                    $galeriaPaths[] = $path;
                }
            }
            if (!empty($galeriaPaths)) {
                $mascota->galeria_fotos = json_encode($galeriaPaths);
                $mascota->save();
            }
        }

        // Procesar arrays JSON
        if (isset($data['enfermedades_cronicas']) && is_array($data['enfermedades_cronicas'])) {
            $mascota->enfermedades_cronicas = json_encode($data['enfermedades_cronicas'], JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        if (isset($data['medicamentos']) && is_array($data['medicamentos'])) {
            $mascota->medicamentos = json_encode($data['medicamentos'], JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        if (isset($data['requisitos_adopcion']) && is_array($data['requisitos_adopcion'])) {
            $mascota->requisitos_adopcion = json_encode($data['requisitos_adopcion'], JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        // Sincronizar razas
        if (!empty($data['razas'])) {
            $mascota->razas()->sync($data['razas']);
        }

        // Sincronizar vacunas
        if (!empty($data['vacunas'])) {
            $vacunasData = [];
            foreach ($data['vacunas'] as $vacunaId) {
                $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()->format('Y-m-d')];
            }
            $mascota->vacunas()->sync($vacunasData);
        }

        return $mascota->load(['razas', 'vacunas']);
    }

    public function updateMascota(int $id, array $data, $files = null)
    {
        $mascota = $this->findMascota($id);

        // Actualizar foto principal
        if (!empty($files['foto_principal']) && $files['foto_principal']->isValid()) {
            if ($mascota->foto_principal) {
                $this->deleteImage($mascota->foto_principal);
            }
            $data['foto_principal'] = $this->uploadImage($files['foto_principal'], 'mascotas');
        }

        $mascota->update($data);

        // Procesar arrays JSON
        if (isset($data['enfermedades_cronicas']) && is_array($data['enfermedades_cronicas'])) {
            $mascota->enfermedades_cronicas = json_encode($data['enfermedades_cronicas'], JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        if (isset($data['medicamentos']) && is_array($data['medicamentos'])) {
            $mascota->medicamentos = json_encode($data['medicamentos'], JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        if (isset($data['requisitos_adopcion']) && is_array($data['requisitos_adopcion'])) {
            $mascota->requisitos_adopcion = json_encode($data['requisitos_adopcion'], JSON_UNESCAPED_UNICODE);
            $mascota->save();
        }

        // Sincronizar razas
        if (!empty($data['razas'])) {
            $mascota->razas()->sync($data['razas']);
        }

        // Sincronizar vacunas
        if (!empty($data['vacunas'])) {
            $vacunasData = [];
            foreach ($data['vacunas'] as $vacunaId) {
                $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()->format('Y-m-d')];
            }
            $mascota->vacunas()->sync($vacunasData);
        }

        return $mascota->load(['razas', 'vacunas']);
    }

    public function deleteMascota(int $id)
    {
        $mascota = $this->findMascota($id);

        if ($mascota->foto_principal) {
            $this->deleteImage($mascota->foto_principal);
        }

        // Eliminar imágenes de galería
        if ($mascota->galeria_fotos) {
            $galeria = is_string($mascota->galeria_fotos)
                ? json_decode($mascota->galeria_fotos, true)
                : $mascota->galeria_fotos;

            if (is_array($galeria)) {
                foreach ($galeria as $foto) {
                    $this->deleteImage($foto);
                }
            }
        }

        $mascota->razas()->detach();
        $mascota->vacunas()->detach();
        $mascota->delete();
    }

    public function toggleDestacada(int $id): Mascota
    {
        $mascota = $this->findMascota($id);
        $mascota->destacada = !$mascota->destacada;
        $mascota->save();
        return $mascota;
    }
}
