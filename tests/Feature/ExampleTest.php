<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * La página principal redirige al catálogo.
     */
    public function test_home_redirects_to_catalog(): void
    {
        $this->get('/')->assertRedirect(route('catalog.index'));
    }
}
