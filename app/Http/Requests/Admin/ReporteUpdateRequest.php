<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReporteUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estado' => 'sometimes|required|in:activo,resuelto,cerrado',
            'solucion' => 'nullable|string',
            'notas_internas' => 'nullable|string',
        ];
    }
}
