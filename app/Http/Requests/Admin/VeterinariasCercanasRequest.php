<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VeterinariasCercanasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'radio' => 'nullable|integer|min:1|max:100',
        ];
    }
}
