<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentationTest extends TestCase
{
    public function test_api_documentation_redirects_to_scramble_docs_ui(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertRedirect('/docs/api');
    }

    public function test_scramble_docs_ui_is_accessible_in_local_environment(): void
    {
        $this->app['env'] = 'local';

        $response = $this->get('/docs/api');

        $response->assertStatus(200);
    }
}
