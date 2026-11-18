<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * Sengaja tanpa RefreshDatabase: seluruh tes memakai database uji
     * (db_data_induk_test) lewat .env.testing, bukan database asli.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
