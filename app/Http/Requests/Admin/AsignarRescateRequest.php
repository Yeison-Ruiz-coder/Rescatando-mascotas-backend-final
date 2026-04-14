<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AsignarRescateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entidad_tipo' => 'required|in:fundacion,veterinaria',
            'entidad_id' => 'required|integer',
        ];
    }
}
