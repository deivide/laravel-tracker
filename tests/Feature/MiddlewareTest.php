<?php

namespace PragmaRX\Tracker\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PragmaRX\Tracker\Tests\EnabledTestCase;
use PragmaRX\Tracker\Vendor\Laravel\Middlewares\Tracker as TrackerMiddleware;

class MiddlewareTest extends EnabledTestCase
{
    /** @test */
    public function middleware_passes_request_through()
    {
        $middleware = new TrackerMiddleware();
        $request = Request::create('/test', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    /** @test */
    public function middleware_does_nothing_when_tracker_disabled()
    {
        $this->app['config']->set('tracker.enabled', false);

        $middleware = new TrackerMiddleware();
        $request = Request::create('/test', 'GET');

        $response = $middleware->handle($request, function ($req) {
            return new Response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }
}
