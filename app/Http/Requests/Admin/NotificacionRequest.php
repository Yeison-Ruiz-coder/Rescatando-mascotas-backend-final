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
        ];
    }
}
