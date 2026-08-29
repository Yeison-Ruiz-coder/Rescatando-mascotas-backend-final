<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_public_api_returns_a_successful_response(): void
    {
        $response = $this->get('/api/mascotas?page=1&per_page=12');

        $response->assertStatus(200);
    }

    public function test_vercel_frontend_origin_is_allowed_for_api_requests(): void
    {
        $origin = 'https://rescatando-mascotas-frontend-final-drab.vercel.app';

        $response = $this->withHeaders([
            'Origin' => $origin,
        ])->get('/api/mascotas?page=1&per_page=12');

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', $origin);
    }
}
