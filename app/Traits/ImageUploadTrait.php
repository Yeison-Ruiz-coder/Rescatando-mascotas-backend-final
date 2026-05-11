<?php
// app/Traits/ImageUploadTrait.php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;  // Solo esta clase necesitas

trait ImageUploadTrait
{
    private $cloudinary;

    private function getCloudinary(): Cloudinary
    {
        if ($this->cloudinary === null) {
            $this->cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                    'api_key'    => env('CLOUDINARY_API_KEY'),
                    'api_secret' => env('CLOUDINARY_API_SECRET'),
                ],
                'url' => ['secure' => true]
            ]);
        }
        return $this->cloudinary;
    }

    /**
     * Subir una imagen a Cloudinary
     */
    protected function uploadImage(UploadedFile $file, string $path, ?string $oldPath = null): string
    {
        try {
            if ($oldPath) {
                $this->deleteImage($oldPath);
            }

            // ✅ Sintaxis correcta v2 del SDK [citation:7]
            $result = $this->getCloudinary()->uploadApi()->upload($file->getRealPath(), [
                'folder' => $path,
                'transformation' => [
                    'quality' => 'auto',
                    'fetch_format' => 'auto'
                ]
            ]);

            Log::info('Imagen subida a Cloudinary', [
                'folder' => $path,
                'url' => $result['secure_url']
            ]);

            return $result['secure_url'];
        } catch (\Exception $e) {
            Log::error('Error subiendo imagen a Cloudinary: ' . $e->getMessage());
            throw new \Exception('Error al subir la imagen: ' . $e->getMessage());
        }
    }

    /**
     * Subir múltiples imágenes
     */
    protected function uploadMultipleImages(array $files, string $path): array
    {
        $urls = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                try {
                    $urls[] = $this->uploadImage($file, $path);
                } catch (\Exception $e) {
                    Log::warning('Error subiendo imagen individual: ' . $e->getMessage());
                }
            }
        }
        return $urls;
    }

    /**
     * Eliminar una imagen de Cloudinary
     */
    protected function deleteImage(?string $path): void
    {
        if (!$path || strpos($path, 'cloudinary.com') === false) {
            return;
        }

        try {
            $publicId = $this->extractPublicIdFromUrl($path);
            if ($publicId) {
                // ✅ Sintaxis correcta para eliminar [citation:1][citation:4]
                $this->getCloudinary()->uploadApi()->destroy($publicId);
                Log::info('Imagen eliminada de Cloudinary', ['public_id' => $publicId]);
            }
        } catch (\Exception $e) {
            Log::error('Error eliminando imagen de Cloudinary: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar múltiples imágenes
     */
    protected function deleteMultipleImages(array $urls): void
    {
        foreach ($urls as $url) {
            $this->deleteImage($url);
        }
    }

    /**
     * Extraer el public_id de una URL de Cloudinary
     */
    private function extractPublicIdFromUrl(string $url): ?string
    {
        if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\.[a-zA-Z]+)?$/', $url, $matches)) {
            return preg_replace('/\.[^.]+$/', '', $matches[1]);
        }
        return null;
    }
}
