<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ComentarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contenido' => 'required|string|min:3|max:1000',
        ];
    }

    public function attributes(): array
    {
        return [
            'contenido' => 'contenido del comentario',
        ];
    }
}
