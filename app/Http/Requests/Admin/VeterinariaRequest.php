<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VeterinariaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('veterinaria') ?? 'null';

        return [
            'Nombre_vet' => 'required|string|max:255',
            'Direccion' => 'required|string|unique:veterinarias,Direccion,' . $id,
            'Telefono' => 'required|string|unique:veterinarias,Telefono,' . $id,
            'Email' => 'required|email|unique:veterinarias,Email,' . $id,
            'servicios' => 'nullable|array',
            'urgencias_24h' => 'boolean',
            'convenios' => 'nullable|array',
            'user_id' => 'nullable|exists:users,id',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'radio_atencion' => 'nullable|integer|min:1|max:100',
            'sitio_web' => 'nullable|url|max:255',
            'horario_atencion' => 'nullable|string',
            'descripcion' => 'nullable|string',
            'logo_url' => 'nullable|string|max:255',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'urgencias_24h' => $this->boolean('urgencias_24h'),
        ]);
    }
}
