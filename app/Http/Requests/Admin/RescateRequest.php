<?php
// app/Http/Requests/Admin/RescateRequest.php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RescateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_rescate'      => 'required|date',
            'lugar_rescate'      => 'required|string|max:255',
            'descripcion_rescate'=> 'required|string',
            'tipo_emergencia'    => 'required|in:herido,abandonado,urgente,otro',
            'prioridad'          => 'required|in:alta,media,baja',
            'lat'                => 'nullable|numeric',
            'lng'                => 'nullable|numeric',
            'estado'             => 'required|in:pendiente,en_proceso,completado,seguimiento',
            'mascota_id'         => 'nullable|exists:mascotas,id',
            'reporte_id'         => 'nullable|exists:reportes,id',
            'usuario_reporto_id' => 'nullable|exists:users,id',
            'nombre_reportante'  => 'nullable|string|max:255',
            'email_reportante'   => 'nullable|email|max:255',
            'telefono_reportante'=> 'nullable|string|max:20',
            'gestionado_por'     => 'nullable|exists:users,id',
            // ===== NUEVOS CAMPOS PARA FOTOS =====
            'foto_principal'         => 'nullable|image|max:5120',
            'galeria_fotos'          => 'nullable|array',
            'galeria_fotos.*'        => 'image|max:5120',
            'fotos_metadata'         => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'galeria_fotos.*.image' => 'Cada archivo debe ser una imagen válida',
            'galeria_fotos.*.max' => 'Cada foto no puede superar los 5MB',
        ];
    }
}
