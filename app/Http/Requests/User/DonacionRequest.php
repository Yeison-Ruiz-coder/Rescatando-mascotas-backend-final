<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class DonacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fundacion_id' => 'required|exists:fundaciones,id',
            'valor_donacion' => 'required|numeric|min:1000',
            'publica' => 'boolean',
            // ===== CAMPOS NUEVOS RECOMENDADOS =====
            'metodo_pago' => 'nullable|string|in:nequi,daviplata,bancolombia,efecty,otros',
            'comentarios' => 'nullable|string|max:500',
            'anonima' => 'nullable|boolean',
            'nombre_donante' => 'nullable|string|max:255|required_if:anonima,false',
            'email_donante' => 'nullable|email|max:255|required_if:anonima,false',
            'telefono_donante' => 'nullable|string|max:20',
            'comprobante_url' => 'nullable|url|max:500',
            'fecha_donacion' => 'nullable|date', // Para donaciones manuales/offline
        ];
    }

    public function messages(): array
    {
        return [
            'fundacion_id.required' => 'Debes seleccionar una fundación',
            'fundacion_id.exists' => 'La fundación seleccionada no existe',
            'valor_donacion.required' => 'El valor de la donación es requerido',
            'valor_donacion.numeric' => 'El valor debe ser un número',
            'valor_donacion.min' => 'El valor mínimo de donación es $1,000 COP',
            'publica.boolean' => 'El campo pública debe ser verdadero o falso',
            'metodo_pago.in' => 'El método de pago no es válido',
            'nombre_donante.required_if' => 'Debes proporcionar tu nombre para donaciones públicas',
            'email_donante.required_if' => 'Debes proporcionar tu email para donaciones públicas',
            'email_donante.email' => 'El email debe ser válido',
            'comprobante_url.url' => 'El comprobante debe ser una URL válida',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'publica' => $this->boolean('publica'),
            'anonima' => $this->boolean('anonima'),
        ]);
    }
}
