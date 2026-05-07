<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('evento') ?? 'null';

        return [
            'nombre_evento' => 'required|string|max:255',
            'lugar_evento' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'fecha_evento' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_evento',
            'imagen_url' => 'nullable|image|max:2048',
            'imagen_public_id' => 'nullable|string|max:255',
            'capacidad_maxima' => 'nullable|integer|min:1',
            'costo' => 'nullable|numeric|min:0',
            'organizador' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:20',
            'email_contacto' => 'nullable|email|max:255',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'categoria' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
        ];
    }
}
