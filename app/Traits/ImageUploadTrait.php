<?php
// app/Traits/ImageUploadTrait.php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Cloudinary\Cloudinary;

trait ImageUploadTrait
{
    private $cloudinary;

    /**
     * Obtener instancia de Cloudinary
     */
    private function getCloudinary(): Cloudinary
    {
        if ($this->cloudinary === null) {
            // ✅ Usar config() en lugar de env()
            $cloudName = config('cloudinary.cloud.cloud_name');
            $apiKey = config('cloudinary.cloud.api_key');
            $apiSecret = config('cloudinary.cloud.api_secret');

            // Validar que las variables existan
            if (empty($cloudName) || empty($apiKey) || empty($apiSecret)) {
                Log::error('Cloudinary no configurado correctamente', [
                    'cloud_name' => $cloudName ? 'presente' : 'vacío',
                    'api_key' => $apiKey ? 'presente' : 'vacío',
                    'api_secret' => $apiSecret ? 'presente' : 'vacío',
                ]);
                throw new \Exception('Cloudinary no está configurado correctamente. Verifica las variables de entorno.');
            }

            $this->cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => $cloudName,
                    'api_key'    => $apiKey,
                    'api_secret' => $apiSecret,
                ],
                'url' => ['secure' => config('cloudinary.url.secure', true)]
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

            $result = $this->getCloudinary()->uploadApi()->upload($file->getRealPath(), [
                'folder' => $path,
                'transformation' => [
                    'quality' => 'auto',
                    'fetch_format' => 'auto',
                    'width' => 800,
                    'height' => 800,
                    'crop' => 'limit'
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
     * Subir múltiples imágenes a Cloudinary
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
    protected function deleteImage(?string $url): void
    {
        if (!$url || strpos($url, 'cloudinary.com') === false) {
            return;
        }

        try {
            $publicId = $this->extractPublicIdFromUrl($url);
            if ($publicId) {
                $this->getCloudinary()->uploadApi()->destroy($publicId);
                Log::info('Imagen eliminada de Cloudinary', ['public_id' => $publicId]);
            }
        } catch (\Exception $e) {
            Log::error('Error eliminando imagen de Cloudinary: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar múltiples imágenes de Cloudinary
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
        // Busca el public_id en la URL de Cloudinary
        // Ejemplo: https://res.cloudinary.com/cloud_name/image/upload/v1234567890/folder/image.jpg
        // Resultado: folder/image
        if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\.[a-zA-Z]+)?$/', $url, $matches)) {
            // Eliminar la extensión del archivo
            return preg_replace('/\.[^.]+$/', '', $matches[1]);
        }
        return null;
    }
}
