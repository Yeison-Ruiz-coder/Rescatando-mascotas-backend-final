<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

trait ImageUploadTrait
{
    protected function uploadImage(?UploadedFile $file, string $path, ?string $oldPath = null): ?string
    {
        if (!$file) return $oldPath;

        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return $file->store($path, 'public');
    }

    protected function uploadMultipleImages(array $files, string $path): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $paths[] = $file->store($path, 'public');
            }
        }
        return $paths;
    }

    protected function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
