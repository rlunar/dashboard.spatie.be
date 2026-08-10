<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testTheDashboardRouteRespondsSuccessfully(): void
    {
        config()->set('app.access_token', 'test-token');

        Http::fake();

        $this->get('/?access-token=test-token')
            ->assertOk();
    }
}
