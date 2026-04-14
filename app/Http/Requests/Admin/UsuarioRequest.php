<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('usuario') ?? 'null';

        return [
            'nombre' => 'required|string|max:255',
            'apellidos' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
            'tipo' => 'required|in:admin,user,veterinaria,fundacion',
            'estado' => 'required|in:activo,inactivo,suspendido,pendiente',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'avatar' => 'nullable|image|max:2048',
            'tipo_documento' => 'nullable|string|max:50',
            'numero_documento' => 'nullable|string|unique:users,numero_documento,' . $id,
            'fecha_nacimiento' => 'nullable|date',
        ];
    }

    public function attributes(): array
    {
        return [
            'numero_documento' => 'número de documento',
        ];
    }
}
