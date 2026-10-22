<?php

namespace Tests\Feature;

use Tests\Feature\Seguridad\SeguridadTestCase;

class ExampleTest extends SeguridadTestCase
{
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
