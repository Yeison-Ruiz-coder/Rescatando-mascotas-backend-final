<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VeterinariaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('veterinaria') ?? 'null';

        return [
            'Nombre_vet' => 'required|string|max:255',
            'Direccion' => 'required|string|unique:veterinarias,Direccion,' . $id,
            'Telefono' => 'required|string|unique:veterinarias,Telefono,' . $id,
            'Email' => 'required|email|unique:veterinarias,Email,' . $id,
            'servicios' => 'nullable|array',
            'urgencias_24h' => 'boolean',
            'convenios' => 'nullable|array',
            'user_id' => 'nullable|exists:users,id',
            // ===== NUEVOS CAMPOS =====
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'radio_atencion' => 'nullable|integer|min:1|max:100',
            'descripcion' => 'nullable|string',
            'horario_atencion' => 'nullable|string',
            'anios_experiencia' => 'nullable|integer|min:0|max:100',
            'servicios_detallados' => 'nullable|array',
            'logo' => 'nullable|image|max:2048',
            'redes_sociales' => 'nullable|array',
            'whatsapp' => 'nullable|string|max:20',
            'sitio_web' => 'nullable|url',
            'verificado' => 'nullable|boolean',
            'precio_consulta' => 'nullable|numeric|min:0',
            'acepta_seguros' => 'nullable|boolean',
            'ciudad' => 'nullable|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'cobertura_zona' => 'nullable|array',
            // Mantener compatibilidad con campos antiguos
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'logo_url' => 'nullable|string|max:255',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'urgencias_24h' => $this->boolean('urgencias_24h'),
            'verificado' => $this->boolean('verificado'),
            'acepta_seguros' => $this->boolean('acepta_seguros'),
        ]);

        // Compatibilidad con latitud/longitud
        if ($this->has('latitud') && !$this->has('lat')) {
            $this->merge(['lat' => $this->latitud]);
        }
        if ($this->has('longitud') && !$this->has('lng')) {
            $this->merge(['lng' => $this->longitud]);
        }
    }
}
