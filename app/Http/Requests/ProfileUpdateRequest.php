<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            // Datos básicos
            'nombre' => ['required', 'string', 'max:255'],
            'apellidos' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],

            // Contacto
            'telefono' => ['nullable', 'string', 'max:20'],
            'direccion' => ['nullable', 'string', 'max:500'],

            // Información personal
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'tipo_documento' => ['nullable', 'string', 'max:50'],
            'numero_documento' => ['nullable', 'string', 'max:50', Rule::unique(User::class)->ignore($this->user()->id)],

            // Avatar
            'avatar' => ['nullable', 'image', 'max:2048'], // 2MB max

            // ===== NUEVOS CAMPOS DEL PERFIL =====
            'biografia' => ['nullable', 'string', 'max:1000'],
            'redes_sociales' => ['nullable', 'array'],
            'redes_sociales.facebook' => ['nullable', 'url'],
            'redes_sociales.instagram' => ['nullable', 'string', 'max:255'],
            'redes_sociales.twitter' => ['nullable', 'url'],
            'redes_sociales.linkedin' => ['nullable', 'url'],

            // Ubicación
            'pais' => ['nullable', 'string', 'max:100'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'codigo_postal' => ['nullable', 'string', 'max:20'],

            // Preferencias
            'idioma' => ['nullable', 'string', 'in:es,en'],
            'tema' => ['nullable', 'string', 'in:light,dark,system'],
            'preferencias_notificaciones' => ['nullable', 'array'],
            'preferencias_notificaciones.email' => ['nullable', 'boolean'],
            'preferencias_notificaciones.push' => ['nullable', 'boolean'],
            'preferencias_notificaciones.sms' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'email.required' => 'El email es obligatorio',
            'email.email' => 'Debe ser un email válido',
            'email.unique' => 'Este email ya está registrado',
            'fecha_nacimiento.before' => 'La fecha de nacimiento debe ser anterior a hoy',
            'avatar.image' => 'El avatar debe ser una imagen',
            'avatar.max' => 'El avatar no puede superar los 2MB',
            'numero_documento.unique' => 'Este número de documento ya está registrado',
            'redes_sociales.facebook.url' => 'El enlace de Facebook debe ser una URL válida',
            'redes_sociales.twitter.url' => 'El enlace de Twitter debe ser una URL válida',
            'idioma.in' => 'El idioma debe ser español o inglés',
            'tema.in' => 'El tema debe ser claro, oscuro o sistema',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Normalizar campos booleanos en preferencias si vienen
        if ($this->has('preferencias_notificaciones')) {
            $prefs = $this->input('preferencias_notificaciones', []);
            $this->merge([
                'preferencias_notificaciones' => [
                    'email' => isset($prefs['email']) ? filter_var($prefs['email'], FILTER_VALIDATE_BOOLEAN) : true,
                    'push' => isset($prefs['push']) ? filter_var($prefs['push'], FILTER_VALIDATE_BOOLEAN) : true,
                    'sms' => isset($prefs['sms']) ? filter_var($prefs['sms'], FILTER_VALIDATE_BOOLEAN) : false,
                ],
            ]);
        }
    }
}
