<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ComentarioMasivoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'accion' => 'required|in:eliminar',
            'comentarios' => 'required|array',
            'comentarios.*' => 'exists:comentarios,id'
        ];
    }

    public function messages(): array
    {
        return [
            'comentarios.*.exists' => 'Uno o más comentarios no existen'
        ];
    }
}
