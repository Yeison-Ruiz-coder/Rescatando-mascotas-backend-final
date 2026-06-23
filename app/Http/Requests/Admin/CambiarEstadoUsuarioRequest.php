<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CambiarEstadoUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => 'required|in:activo,inactivo,suspendido,pendiente'
        ];
    }

    public function messages(): array
    {
        return [
            'estado.required' => 'El estado es requerido',
            'estado.in' => 'El estado no es válido. Los estados permitidos son: activo, inactivo, suspendido, pendiente',
        ];
    }
}
