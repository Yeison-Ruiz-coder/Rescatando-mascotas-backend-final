<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class DonacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('donacion') ?? 'null';

        return [
            'valor_donacion' => 'required|numeric|min:1000',
            'user_id' => 'nullable|exists:users,id',
            'fundacion_id' => 'nullable|exists:fundaciones,id',
            'publica' => 'boolean',
            'fecha_donacion' => 'nullable|date',
            'metodo_pago' => 'nullable|string|max:100',
            'comentarios' => 'nullable|string|max:500',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'publica' => $this->boolean('publica'),
        ]);
    }
}
