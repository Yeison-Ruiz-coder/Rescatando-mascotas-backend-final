<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_reporte' => 'required|in:maltrato,abandono,extraviado,encontrado,otro',
            'descripcion' => 'required|string|min:10',
            'ubicacion' => 'required|string|max:255',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'user_id' => 'required|exists:users,id',
            'foto_url' => 'nullable|image|max:2048',
            'contacto_telefono' => 'nullable|string|max:20',
            'contacto_email' => 'nullable|email|max:255',
            'datos_animal' => 'nullable|array',
        ];
    }
}
