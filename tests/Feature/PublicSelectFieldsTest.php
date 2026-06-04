<?php

namespace Tests\Feature;

use App\Models\Donacion;
use App\Models\Fundacion;
use App\Models\Mascota;
use App\Models\Notificacion;
use App\Models\Raza;
use App\Models\Solicitud;
use App\Models\Suscripcion;
use App\Models\User;
use App\Models\Veterinaria;
use Tests\TestCase;

class PublicSelectFieldsTest extends TestCase
{
    public function test_public_select_fields_respects_model_whitelist(): void
    {
        app('request')->query->set('fields', 'nombre_mascota,invalid_field');

        try {
            $columns = Mascota::query()->selectFields()->getQuery()->columns;

            $this->assertContains('nombre_mascota', $columns);
            $this->assertNotContains('invalid_field', $columns);
            $this->assertCount(1, $columns);
        } finally {
            app('request')->query->remove('fields');
        }
    }

    public function test_public_select_fields_works_for_fundacion_and_veterinaria(): void
    {
        app('request')->query->set('fields', 'Nombre_1,Nombre_vet,invalid_field');

        try {
            $fundacionColumns = Fundacion::query()->selectFields()->getQuery()->columns;
            $veterinariaColumns = Veterinaria::query()->selectFields()->getQuery()->columns;

            $this->assertContains('Nombre_1', $fundacionColumns);
            $this->assertNotContains('invalid_field', $fundacionColumns);
            $this->assertContains('Nombre_vet', $veterinariaColumns);
            $this->assertNotContains('invalid_field', $veterinariaColumns);
        } finally {
            app('request')->query->remove('fields');
        }
    }

    public function test_public_select_fields_works_for_suscripcion(): void
    {
        app('request')->query->set('fields', 'monto_mensual,invalid_field');

        try {
            $columns = Suscripcion::query()->selectFields()->getQuery()->columns;

            $this->assertContains('monto_mensual', $columns);
            $this->assertNotContains('invalid_field', $columns);
            $this->assertCount(1, $columns);
        } finally {
            app('request')->query->remove('fields');
        }
    }

    public function test_public_select_fields_works_for_remaining_models(): void
    {
        app('request')->query->set('fields', 'valor_donacion,invalid_field');
        try {
            $donacionColumns = Donacion::query()->selectFields()->getQuery()->columns;
            $this->assertContains('valor_donacion', $donacionColumns);
            $this->assertNotContains('invalid_field', $donacionColumns);
        } finally {
            app('request')->query->remove('fields');
        }

        app('request')->query->set('fields', 'titulo,invalid_field');
        try {
            $notificacionColumns = Notificacion::query()->selectFields()->getQuery()->columns;
            $this->assertContains('titulo', $notificacionColumns);
            $this->assertNotContains('invalid_field', $notificacionColumns);
        } finally {
            app('request')->query->remove('fields');
        }

        app('request')->query->set('fields', 'nombre_raza,invalid_field');
        try {
            $razaColumns = Raza::query()->selectFields()->getQuery()->columns;
            $this->assertContains('nombre_raza', $razaColumns);
            $this->assertNotContains('invalid_field', $razaColumns);
        } finally {
            app('request')->query->remove('fields');
        }

        app('request')->query->set('fields', 'tipo_solicitud,invalid_field');
        try {
            $solicitudColumns = Solicitud::query()->selectFields()->getQuery()->columns;
            $this->assertContains('tipo_solicitud', $solicitudColumns);
            $this->assertNotContains('invalid_field', $solicitudColumns);
        } finally {
            app('request')->query->remove('fields');
        }

        app('request')->query->set('fields', 'nombre,invalid_field');
        try {
            $userColumns = User::query()->selectFields()->getQuery()->columns;
            $this->assertContains('nombre', $userColumns);
            $this->assertNotContains('invalid_field', $userColumns);
        } finally {
            app('request')->query->remove('fields');
        }
    }
}
