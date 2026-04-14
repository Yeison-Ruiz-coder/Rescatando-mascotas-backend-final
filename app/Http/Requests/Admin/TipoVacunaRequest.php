<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TipoVacunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('tipo_vacuna') ?? 'null';

        return [
            'nombre_vacuna' => 'required|string|max:255|unique:tipos_vacunas,nombre_vacuna,' . $id,
            'descripcion' => 'nullable|string',
            'frecuencia_dias' => 'nullable|integer|min:1',
            'edad_minima_dias' => 'nullable|integer|min:0',
            'edad_maxima_dias' => 'nullable|integer|min:0|gt:edad_minima_dias',
            'especie' => 'nullable|string|max:100',
            'obligatoria' => 'boolean',
            'activa' => 'boolean',
            'informacion_adicional' => 'nullable|string',
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_vacuna' => 'nombre de la vacuna',
            'frecuencia_dias' => 'frecuencia en días',
            'edad_minima_dias' => 'edad mínima en días',
            'edad_maxima_dias' => 'edad máxima en días',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Convertir checkbox values a boolean
        $this->merge([
            'obligatoria' => $this->boolean('obligatoria'),
            'activa' => $this->boolean('activa'),
        ]);
    }
}
