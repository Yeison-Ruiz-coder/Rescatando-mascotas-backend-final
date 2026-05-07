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

        // Procesar nuevos campos JSON
        if (isset($data['enfermedades_cronicas']) && is_array($data['enfermedades_cronicas'])) {
            $data['enfermedades_cronicas'] = json_encode($data['enfermedades_cronicas'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['medicamentos']) && is_array($data['medicamentos'])) {
            $data['medicamentos'] = json_encode($data['medicamentos'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['requisitos_adopcion']) && is_array($data['requisitos_adopcion'])) {
            $data['requisitos_adopcion'] = json_encode($data['requisitos_adopcion'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['padrinos']) && is_array($data['padrinos'])) {
            $data['padrinos'] = json_encode($data['padrinos'], JSON_UNESCAPED_UNICODE);
        }

        // Valores por defecto
        $data['vistas'] = $data['vistas'] ?? 0;
        $data['interesados'] = $data['interesados'] ?? 0;
        $data['destacada'] = $data['destacada'] ?? false;
        $data['fecha_publicacion'] = $data['fecha_publicacion'] ?? now();

        // Crear mascota
        $mascota = Mascota::create($data);

        // Sincronizar relaciones
        if (isset($data['razas'])) {
            $mascota->razas()->sync($data['razas']);
        }

        if (isset($data['vacunas'])) {
            $vacunasData = [];
            foreach ($data['vacunas'] as $vacunaId) {
                $vacunasData[$vacunaId] = ['fecha_aplicacion' => $data['fecha_vacuna'] ?? now()];
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
            if (is_string($galeria)) {
                $galeria = json_decode($galeria, true) ?? [];
            }
            $nuevasFotos = $this->uploadMultipleImages($files['galeria_fotos'], 'mascotas/galeria');
            $data['galeria_fotos'] = array_merge($galeria, $nuevasFotos);
        }

        // Procesar campos JSON
        if (isset($data['enfermedades_cronicas']) && is_array($data['enfermedades_cronicas'])) {
            $data['enfermedades_cronicas'] = json_encode($data['enfermedades_cronicas'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['medicamentos']) && is_array($data['medicamentos'])) {
            $data['medicamentos'] = json_encode($data['medicamentos'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['requisitos_adopcion']) && is_array($data['requisitos_adopcion'])) {
            $data['requisitos_adopcion'] = json_encode($data['requisitos_adopcion'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($data['padrinos']) && is_array($data['padrinos'])) {
            $data['padrinos'] = json_encode($data['padrinos'], JSON_UNESCAPED_UNICODE);
        }

        $mascota->update($data);

        if (isset($data['razas'])) {
            $mascota->razas()->sync($data['razas']);
        }

        if (isset($data['vacunas'])) {
            $vacunasData = [];
            foreach ($data['vacunas'] as $vacunaId) {
                $vacunasData[$vacunaId] = ['fecha_aplicacion' => $data['fecha_vacuna'] ?? now()];
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
            $galeria = is_string($mascota->galeria_fotos) ? json_decode($mascota->galeria_fotos, true) : $mascota->galeria_fotos;
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
        $mascota = Mascota::findOrFail($id);
        $mascota->destacada = !$mascota->destacada;
        $mascota->save();
        return $mascota;
    }

    public function incrementarVistas(int $id): void
    {
        Mascota::where('id', $id)->increment('vistas');
    }

    public function incrementarInteresados(int $id): void
    {
        Mascota::where('id', $id)->increment('interesados');
    }

    public function addGaleriaFotos(Mascota $mascota, array $nuevasFotos): void
    {
        $galeriaActual = $mascota->galeria_fotos;

        if (is_string($galeriaActual)) {
            $galeriaActual = json_decode($galeriaActual, true) ?? [];
        } elseif (!is_array($galeriaActual)) {
            $galeriaActual = [];
        }

        $nuevasUrls = [];
        foreach ($nuevasFotos as $foto) {
            if ($foto && $foto->isValid()) {
                $url = $this->uploadImage($foto, 'mascotas/galeria');
                $nuevasUrls[] = $url;
            }
        }

        $galeriaFinal = array_merge($galeriaActual, $nuevasUrls);
        $mascota->galeria_fotos = json_encode($galeriaFinal);
        $mascota->save();
    }

    public function removeGaleriaFoto(Mascota $mascota, string $fotoUrl): void
    {
        $galeria = $mascota->galeria_fotos;

        if (is_string($galeria)) {
            $galeria = json_decode($galeria, true) ?? [];
        }

        $this->deleteImage($fotoUrl);

        $galeria = array_filter($galeria, fn($url) => $url !== $fotoUrl);

        $mascota->galeria_fotos = json_encode(array_values($galeria));
        $mascota->save();
    }
}
