<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

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
            'avatar_public_id' => 'nullable|string|max:255',
            'tipo_documento' => 'nullable|string|max:50',
            'numero_documento' => 'nullable|string|unique:users,numero_documento,' . $id,
            'fecha_nacimiento' => 'nullable|date',
            // ===== NUEVOS CAMPOS =====
            'biografia' => 'nullable|string|max:1000',
            'redes_sociales' => 'nullable|array',
            'redes_sociales.facebook' => 'nullable|url',
            'redes_sociales.instagram' => 'nullable|string|max:255',
            'redes_sociales.twitter' => 'nullable|string|max:255',
            'pais' => 'nullable|string|max:100',
            'ciudad' => 'nullable|string|max:100',
            'codigo_postal' => 'nullable|string|max:20',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'idioma' => 'nullable|string|in:es,en',
            'tema' => 'nullable|string|in:light,dark',
        ];
    }

    public function attributes(): array
    {
        return [
            'numero_documento' => 'número de documento',
            'redes_sociales.facebook' => 'Facebook',
            'redes_sociales.instagram' => 'Instagram',
            'redes_sociales.twitter' => 'Twitter',
        ];
    }
}
