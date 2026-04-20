<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

trait ImageUploadTrait
{
    protected function uploadImage(UploadedFile $file, string $path, ?string $oldPath = null): string
    {
        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($path, $fileName, 'public');
    }

    protected function uploadMultipleImages(array $files, string $path): array
    {
        $paths = [];
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $paths[] = $file->storeAs($path, $fileName, 'public');
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
