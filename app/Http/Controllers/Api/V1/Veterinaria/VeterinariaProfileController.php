<?php

namespace App\Http\Controllers\Api\V1\Veterinaria;

use App\Http\Controllers\Controller;
use App\Services\Veterinaria\VeterinariaProfileService;
use App\Traits\ApiResponses;
use App\Traits\TransactionTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VeterinariaProfileController extends Controller
{
    use ApiResponses, TransactionTrait;

    protected VeterinariaProfileService $profileService;

    public function __construct(VeterinariaProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function show(Request $request)
    {
        $profile = $this->profileService->getCompleteProfile($request->user());
        return $this->successResponse($profile, 'Perfil obtenido exitosamente');
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Datos de User
            'nombre' => 'sometimes|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'biografia' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|max:2048',

            // Datos específicos de Veterinaria
            'Nombre_vet' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string|max:2000',
            'anios_experiencia' => 'nullable|integer|min:0',
            'Direccion' => 'nullable|string|max:500',
            'ciudad' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'Email' => 'nullable|email',
            'whatsapp' => 'nullable|string|max:20',
            'sitio_web' => 'nullable|url',
            'servicios' => 'nullable|array',
            'servicios_detallados' => 'nullable|array',
            'equipo_medico' => 'nullable|array',
            'horario_atencion' => 'nullable|string|max:500',
            'urgencias_24h' => 'nullable|boolean',
            'precio_consulta' => 'nullable|numeric|min:0',
            'acepta_seguros' => 'nullable|boolean',
            'convenios' => 'nullable|array',
            'cobertura_zona' => 'nullable|array',
            'lat' => 'nullable|numeric|between:-90,90',
            'lng' => 'nullable|numeric|between:-180,180',
            'radio_atencion' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $user = $this->runInTransaction(
                fn() => $this->profileService->updateCompleteProfile(
                    $request->user(),
                    $request->all(),
                    $request->file('avatar')
                ),
                'Error al actualizar perfil'
            );

            return $this->successResponse($user, 'Perfil actualizado exitosamente');
        } catch (\Exception $e) {
            return $this->errorResponse('Error al actualizar perfil', $e->getMessage(), 500);
        }
    }

    public function updateGeneralInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'Nombre_vet' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string|max:2000',
            'anios_experiencia' => 'nullable|integer|min:0',
            'Direccion' => 'nullable|string|max:500',
            'ciudad' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'Email' => 'nullable|email',
            'whatsapp' => 'nullable|string|max:20',
            'sitio_web' => 'nullable|url',
            'precio_consulta' => 'nullable|numeric|min:0',
            'acepta_seguros' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $veterinaria = $this->profileService->updateGeneralInfo(
                $request->user(),
                $request->all()
            );

            return $this->successResponse($veterinaria, 'Información actualizada');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', $e->getMessage(), 500);
        }
    }

    public function updateServices(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'servicios' => 'nullable|array',
            'servicios_detallados' => 'nullable|array',
            'equipo_medico' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $veterinaria = $this->profileService->updateServices(
                $request->user(),
                $request->only(['servicios', 'servicios_detallados', 'equipo_medico'])
            );

            return $this->successResponse($veterinaria, 'Servicios actualizados');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', $e->getMessage(), 500);
        }
    }

    public function updateSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'horario_atencion' => 'nullable|string|max:500',
            'urgencias_24h' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $veterinaria = $this->profileService->updateSchedule(
                $request->user(),
                $request->horario_atencion,
                $request->urgencias_24h ?? false
            );

            return $this->successResponse($veterinaria, 'Horario actualizado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', $e->getMessage(), 500);
        }
    }

    public function uploadLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $veterinaria = $this->profileService->uploadLogo(
                $request->user(),
                $request->file('logo')
            );

            return $this->successResponse([
                'logo' => $veterinaria->logo,
                'logo_public_id' => $veterinaria->logo_public_id
            ], 'Logo actualizado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', $e->getMessage(), 500);
        }
    }

    public function deleteLogo(Request $request)
    {
        try {
            $veterinaria = $this->profileService->deleteLogo($request->user());
            return $this->successResponse(null, 'Logo eliminado');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', $e->getMessage(), 500);
        }
    }

    public function addGalleryPhotos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fotos' => 'required|array',
            'fotos.*' => 'image|mimes:jpeg,png,jpg|max:5120'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $veterinaria = $this->profileService->addGalleryPhotos(
                $request->user(),
                $request->file('fotos')
            );

            return $this->successResponse([
                'galeria_fotos' => $veterinaria->galeria_fotos
            ], 'Fotos agregadas a la galería');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', $e->getMessage(), 500);
        }
    }

    public function removeGalleryPhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'photo_url' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Error de validación', $validator->errors(), 422);
        }

        try {
            $veterinaria = $this->profileService->removeGalleryPhoto(
                $request->user(),
                $request->photo_url
            );

            return $this->successResponse($veterinaria, 'Foto eliminada de la galería');
        } catch (\Exception $e) {
            return $this->errorResponse('Error', $e->getMessage(), 500);
        }
    }
}
