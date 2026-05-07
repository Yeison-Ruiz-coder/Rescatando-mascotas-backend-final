<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NotificacionMasivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contenido' => 'required|string',
            'titulo' => 'nullable|string|max:255', // NUEVO
            'tipo_destinatarios' => 'required|in:todos,usuarios,administradores,fundaciones,veterinarias',
            'fecha_envio' => 'nullable|date',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
            // ===== NUEVOS CAMPOS =====
            'tipo' => 'nullable|in:info,success,warning,error,alert',
            'prioridad' => 'nullable|in:baja,media,alta,urgente',
            'url_accion' => 'nullable|string|max:255',
            'expira_en' => 'nullable|date',
        ];
    }
}
