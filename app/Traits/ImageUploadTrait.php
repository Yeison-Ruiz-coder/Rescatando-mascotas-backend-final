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
            $cloudName = config('cloudinary.cloud.cloud_name');
            $apiKey = config('cloudinary.cloud.api_key');
            $apiSecret = config('cloudinary.cloud.api_secret');

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
     * Subir una imagen a Cloudinary con configuración optimizada
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
                    // Calidad y formato optimizados
                    'quality' => 'auto:best',
                    'fetch_format' => 'auto',

                    // Tamaño máximo para cubrir todos los casos
                    'width' => 1600,
                    'height' => 1600,
                    'crop' => 'limit',

                    // Optimizaciones adicionales
                    'dpr' => 'auto',
                    'flags' => 'lossy',
                    'progressive' => 'true',
                    'effect' => 'sharpen:50',
                ],

                // Generar versiones pre-calculadas para diferentes usos
                'eager' => [
                    // Versión para cards (400x300)
                    [
                        'transformation' => [
                            'width' => 400,
                            'height' => 300,
                            'crop' => 'fill',
                            'quality' => 'auto:good',
                            'fetch_format' => 'auto'
                        ],
                        'format' => 'webp'
                    ],
                    // Versión para thumbnails (100x100)
                    [
                        'transformation' => [
                            'width' => 100,
                            'height' => 100,
                            'crop' => 'thumb',
                            'gravity' => 'auto',
                            'quality' => 'auto:eco',
                            'fetch_format' => 'auto'
                        ],
                        'format' => 'webp'
                    ],
                    // Versión para mobile (600x600)
                    [
                        'transformation' => [
                            'width' => 600,
                            'height' => 600,
                            'crop' => 'limit',
                            'quality' => 'auto:good',
                            'fetch_format' => 'auto'
                        ],
                        'format' => 'webp'
                    ]
                ],

                // Configuración de archivo
                'use_filename' => true,
                'unique_filename' => true,
                'overwrite' => false,
                'invalidate' => true
            ]);

            Log::info('Imagen subida a Cloudinary', [
                'folder' => $path,
                'url' => $result['secure_url'],
                'size' => round(($result['bytes'] ?? 0) / 1024, 2) . ' KB',
                'format' => $result['format'] ?? 'unknown'
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
        if (preg_match('/\/upload\/(?:v\d+\/)?(.+?)(?:\.[a-zA-Z]+)?$/', $url, $matches)) {
            return preg_replace('/\.[^.]+$/', '', $matches[1]);
        }
        return null;
    }

    /**
     * Obtener URL con transformaciones personalizadas
     * Útil para el frontend o para generar URLs bajo demanda
     */
    protected function getTransformedUrl(string $url, array $transformations = []): string
    {
        if (!$url || strpos($url, 'cloudinary.com') === false) {
            return $url;
        }

        $parts = explode('/upload/', $url);
        $baseUrl = $parts[0] . '/upload/';
        $path = $parts[1] ?? '';

        // Transformaciones por defecto
        $defaultTransform = [
            'quality' => 'auto:good',
            'fetch_format' => 'auto'
        ];

        $finalTransform = array_merge($defaultTransform, $transformations);

        // Construir string de transformación
        $transformString = '';
        foreach ($finalTransform as $key => $value) {
            if (!empty($transformString)) {
                $transformString .= ',';
            }
            $transformString .= $key . '_' . $value;
        }

        return $baseUrl . $transformString . '/' . $path;
    }

    /**
     * Obtener URL para diferentes tamaños predefinidos
     */
    protected function getImageSizeUrl(string $url, string $size = 'medium'): string
    {
        $sizes = [
            'thumbnail' => ['width' => 100, 'height' => 100, 'crop' => 'thumb'],
            'small' => ['width' => 300, 'height' => 200, 'crop' => 'fill'],
            'medium' => ['width' => 600, 'height' => 400, 'crop' => 'fill'],
            'large' => ['width' => 1200, 'height' => 800, 'crop' => 'limit'],
            'featured' => ['width' => 800, 'height' => 600, 'crop' => 'fill'],
        ];

        $sizeConfig = $sizes[$size] ?? $sizes['medium'];

        return $this->getTransformedUrl($url, $sizeConfig);
    }
}
