<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_redirects_to_english_portfolio(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/en');
    }

    public function test_english_portfolio_page_loads_successfully(): void
    {
        $response = $this->get('/en');

        $response->assertOk();
    }

    public function test_indonesian_portfolio_page_loads_successfully(): void
    {
        $response = $this->get('/id');

        $response->assertOk();
    }
}
