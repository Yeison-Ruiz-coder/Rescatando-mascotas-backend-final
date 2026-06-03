<?php

namespace Tests\Feature;

use App\Models\Mascota;
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
}
