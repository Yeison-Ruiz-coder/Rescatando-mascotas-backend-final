<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DonacionReporteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'fundacion_id' => 'nullable|exists:fundaciones,id',
            'agrupacion' => 'nullable|in:dia,mes,fundacion',
        ];
    }
}
