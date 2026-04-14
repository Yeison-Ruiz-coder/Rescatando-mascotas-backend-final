<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class NecesidadesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'necesidades' => 'required|array',
            'necesidades.*' => 'string|max:255',
        ];
    }
}
