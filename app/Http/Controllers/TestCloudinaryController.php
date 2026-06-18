<?php

namespace App\Http\Controllers;

use Cloudinary\Cloudinary;
use Illuminate\Http\Request;

class TestCloudinaryController extends Controller
{
    public function test()
    {
        try {
            $cloudinary = new Cloudinary([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud.cloud_name'),
                    'api_key' => config('cloudinary.cloud.api_key'),
                    'api_secret' => config('cloudinary.cloud.api_secret'),
                ],
                'url' => ['secure' => true]
            ]);

            // Subir imagen de prueba
            $result = $cloudinary->uploadApi()->upload(
                'https://res.cloudinary.com/demo/image/upload/sample.jpg',
                ['folder' => 'test']
            );

            return response()->json([
                'success' => true,
                'message' => 'Cloudinary funciona correctamente',
                'url' => $result['secure_url']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error con Cloudinary: ' . $e->getMessage()
            ], 500);
        }
    }
}
