<?php

namespace App\Services;

use App\Models\Mascota;
use App\Traits\ImageUploadTrait;

class MascotaService
{
    use ImageUploadTrait;

    public function create(array $data, array $files = []): Mascota
    {
        // Procesar imágenes
        if (isset($files['foto_principal'])) {
            $data['foto_principal'] = $this->uploadImage($files['foto_principal'], 'mascotas');
        }

        if (isset($files['galeria_fotos'])) {
            $data['galeria_fotos'] = $this->uploadMultipleImages($files['galeria_fotos'], 'mascotas/galeria');
        }

        // Crear mascota
        $mascota = Mascota::create($data);

        // Sincronizar relaciones
        if (isset($data['razas'])) {
            $mascota->razas()->sync($data['razas']);
        }

        if (isset($data['vacunas'])) {
            $vacunasData = [];
            foreach ($data['vacunas'] as $vacunaId) {
                $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()];
            }
            $mascota->vacunas()->sync($vacunasData);
        }

        return $mascota;
    }

    public function update(int $id, array $data, array $files = []): Mascota
    {
        $mascota = Mascota::findOrFail($id);

        // Procesar imágenes
        if (isset($files['foto_principal'])) {
            $data['foto_principal'] = $this->uploadImage($files['foto_principal'], 'mascotas', $mascota->foto_principal);
        }

        if (isset($files['galeria_fotos'])) {
            $galeria = $mascota->galeria_fotos ?? [];
            $nuevasFotos = $this->uploadMultipleImages($files['galeria_fotos'], 'mascotas/galeria');
            $data['galeria_fotos'] = array_merge($galeria, $nuevasFotos);
        }

        $mascota->update($data);

        if (isset($data['razas'])) {
            $mascota->razas()->sync($data['razas']);
        }

        if (isset($data['vacunas'])) {
            $vacunasData = [];
            foreach ($data['vacunas'] as $vacunaId) {
                $vacunasData[$vacunaId] = ['fecha_aplicacion' => now()];
            }
            $mascota->vacunas()->sync($vacunasData);
        }

        return $mascota;
    }

    public function delete(int $id): void
    {
        $mascota = Mascota::findOrFail($id);

        // Eliminar imágenes
        $this->deleteImage($mascota->foto_principal);
        if ($mascota->galeria_fotos) {
            foreach ($mascota->galeria_fotos as $foto) {
                $this->deleteImage($foto);
            }
        }

        $mascota->razas()->detach();
        $mascota->vacunas()->detach();
        $mascota->delete();
    }
}
