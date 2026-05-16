<?php

namespace App\Services\Entity;

use App\Models\Evento;
use App\Traits\ImageUploadTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventoEntityService
{
    use ImageUploadTrait;

    /**
     * Obtener la entidad (fundación) del usuario autenticado
     */
    public function getEntidad()
    {
        $user = Auth::user();

        if ($user->tipo === 'fundacion') {
            return $user->fundacion;
        }
        return null;
    }

    /**
     * Obtener todos los eventos de la fundación
     */
    public function getMisEventos()
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        return Evento::where('fundacion_id', $entidad->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Encontrar un evento específico de la fundación
     */
    public function findEvento(int $id)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        $evento = Evento::where('fundacion_id', $entidad->id)
            ->where('id', $id)
            ->first();

        if (!$evento) {
            throw new ModelNotFoundException('Evento no encontrado');
        }

        return $evento;
    }

    /**
     * Crear un nuevo evento
     */
    public function createEvento(array $data, $imagen = null)
    {
        $entidad = $this->getEntidad();

        if (!$entidad) {
            throw new \Exception('Perfil de fundación no encontrado');
        }

        // Datos básicos del evento
        $eventoData = [
            'nombre_evento' => $data['nombre_evento'],
            'lugar_evento' => $data['lugar_evento'],
            'descripcion' => $data['descripcion'],
            'fecha_evento' => $data['fecha_evento'],
            'fundacion_id' => $entidad->id,
            'tipo' => 'fundacion',
            'likes' => 0,
        ];

        // Campos opcionales
        if (isset($data['fecha_fin'])) {
            $eventoData['fecha_fin'] = $data['fecha_fin'];
        }
        if (isset($data['capacidad_maxima'])) {
            $eventoData['capacidad_maxima'] = $data['capacidad_maxima'];
        }
        if (isset($data['costo'])) {
            $eventoData['costo'] = $data['costo'];
        }
        if (isset($data['organizador'])) {
            $eventoData['organizador'] = $data['organizador'];
        }
        if (isset($data['telefono_contacto'])) {
            $eventoData['telefono_contacto'] = $data['telefono_contacto'];
        }
        if (isset($data['email_contacto'])) {
            $eventoData['email_contacto'] = $data['email_contacto'];
        }
        if (isset($data['categoria'])) {
            $eventoData['categoria'] = $data['categoria'];
        }
        if (isset($data['tags']) && is_array($data['tags'])) {
            $eventoData['tags'] = json_encode($data['tags'], JSON_UNESCAPED_UNICODE);
        }

        // Crear el evento
        $evento = Evento::create($eventoData);

        // Subir imagen a Cloudinary si existe
        if ($imagen) {
            $evento->imagen_url = $this->uploadImage($imagen, 'eventos');
            $evento->imagen_public_id = null;
            $evento->save();
        }

        return $evento;
    }

    /**
     * Actualizar un evento existente
     * CORREGIDO: Ahora elimina la imagen anterior antes de subir la nueva
     */
    public function updateEvento(int $id, array $data, $imagen = null)
    {
        $evento = $this->findEvento($id);

        // Actualizar campos que vienen en la petición
        if (isset($data['nombre_evento'])) {
            $evento->nombre_evento = $data['nombre_evento'];
        }
        if (isset($data['lugar_evento'])) {
            $evento->lugar_evento = $data['lugar_evento'];
        }
        if (isset($data['descripcion'])) {
            $evento->descripcion = $data['descripcion'];
        }
        if (isset($data['fecha_evento'])) {
            $evento->fecha_evento = $data['fecha_evento'];
        }
        if (isset($data['fecha_fin'])) {
            $evento->fecha_fin = $data['fecha_fin'];
        }
        if (isset($data['capacidad_maxima'])) {
            $evento->capacidad_maxima = $data['capacidad_maxima'];
        }
        if (isset($data['costo'])) {
            $evento->costo = $data['costo'];
        }
        if (isset($data['organizador'])) {
            $evento->organizador = $data['organizador'];
        }
        if (isset($data['telefono_contacto'])) {
            $evento->telefono_contacto = $data['telefono_contacto'];
        }
        if (isset($data['email_contacto'])) {
            $evento->email_contacto = $data['email_contacto'];
        }
        if (isset($data['categoria'])) {
            $evento->categoria = $data['categoria'];
        }
        if (isset($data['tags']) && is_array($data['tags'])) {
            $evento->tags = json_encode($data['tags'], JSON_UNESCAPED_UNICODE);
        }

        // ✅ CORREGIDO: Actualizar imagen si viene una nueva (eliminando la anterior)
        if ($imagen) {
            // Eliminar imagen anterior si existe
            if ($evento->imagen_url) {
                $this->deleteImage($evento->imagen_url);
            }
            // Subir nueva imagen
            $evento->imagen_url = $this->uploadImage($imagen, 'eventos');
            $evento->imagen_public_id = null;
        }

        $evento->save();
        return $evento;
    }

    /**
     * Eliminar un evento
     */
    public function deleteEvento(int $id)
    {
        $evento = $this->findEvento($id);

        // Eliminar imagen de Cloudinary si existe
        if ($evento->imagen_url) {
            $this->deleteImage($evento->imagen_url);
        }

        $evento->delete();
    }

    /**
     * Obtener evento por ID (sin verificar fundación - para admin)
     */
    public function findEventoById(int $id): Evento
    {
        $evento = Evento::find($id);

        if (!$evento) {
            throw new ModelNotFoundException('Evento no encontrado');
        }

        return $evento;
    }

    /**
     * Obtener todos los eventos (para admin)
     */
    public function getAllEventos()
    {
        return Evento::with('fundacion')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
