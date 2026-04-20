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

        $mascota = new Mascota();
        $mascota->fundacion_id = $fundacion->id;
        $mascota->nombre_mascota = $data['nombre_mascota'];
        $mascota->especie = $data['especie'];
        $mascota->edad_aprox = $data['edad_aprox'];
        $mascota->genero = $data['genero'];
        $mascota->estado = $data['estado'];
        $mascota->lugar_rescate = $data['lugar_rescate'] ?? null;
        $mascota->descripcion = $data['descripcion'];
        $mascota->condiciones_especiales = $data['condiciones_especiales'] ?? null;
        $mascota->necesita_hogar_temporal = $data['necesita_hogar_temporal'] ?? false;
        $mascota->apto_con_ninos = $data['apto_con_ninos'] ?? true;
        $mascota->apto_con_otros_animales = $data['apto_con_otros_animales'] ?? true;
        $mascota->fecha_ingreso = $data['fecha_ingreso'] ?? now();

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
                    Log::info('Foto de galería guardada', ['path' => $path]);
                }
            }
            if (!empty($galeriaPaths)) {
                $mascota->galeria_fotos = json_encode($galeriaPaths);
                $mascota->save();
                Log::info('Galería guardada', ['count' => count($galeriaPaths)]);
            }
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

        $mascota->update($data);

        // Actualizar foto principal
        if (!empty($files['foto_principal']) && $files['foto_principal']->isValid()) {
            if ($mascota->foto_principal) {
                $this->deleteImage($mascota->foto_principal);
            }
            $mascota->foto_principal = $this->uploadImage($files['foto_principal'], 'mascotas');
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
}
