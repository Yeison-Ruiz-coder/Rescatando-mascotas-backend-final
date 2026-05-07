<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NotificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contenido' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'fecha_envio' => 'nullable|date',
            // ===== NUEVOS CAMPOS =====
            'titulo' => 'nullable|string|max:255',
            'tipo' => 'nullable|in:info,success,warning,error,alert',
            'icono' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'url_accion' => 'nullable|string|max:255',
            'texto_accion' => 'nullable|string|max:100',
            'metadata' => 'nullable|array',
            'prioridad' => 'nullable|in:baja,media,alta,urgente',
            'expira_en' => 'nullable|date',
            'enviada_email' => 'nullable|boolean',
            'enviada_push' => 'nullable|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'enviada_email' => $this->boolean('enviada_email'),
            'enviada_push' => $this->boolean('enviada_push'),
        ]);
    }
}
