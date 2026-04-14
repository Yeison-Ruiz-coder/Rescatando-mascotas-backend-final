<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ConvertirRescateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fecha_rescate' => 'required|date',
            'lugar_rescate' => 'required|string|max:255',
            'descripcion_rescate' => 'required|string',
        ];
    }
}
