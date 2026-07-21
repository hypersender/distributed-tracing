<?php

use Hypersender\DistributedTracing\Middleware\AddRequestId;
use Hypersender\DistributedTracing\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function () {
    Route::get('/test', fn () => response()->json(['ok' => true]));
});

it('generates a UUID and sets it on request and response', function () {
    $this->app['router']->getRoutes()->refreshNameLookups();

    $response = $this->get('/test');

    $response->assertHeader('s-request-id');
    $requestId = $response->headers->get('s-request-id');

    expect($requestId)->toBeString()->not->toBeEmpty();
    expect(strlen($requestId))->toBe(36); // UUID length
});

it('sets s-request-id in Laravel Context', function () {
    $this->get('/test');

    $contextId = Context::get('s-request-id');
    expect($contextId)->toBeString()->not->toBeEmpty();
    expect(strlen($contextId))->toBe(36);
});

it('provides getRequestId macro on Request', function () {
    $capturedId = null;
    Route::get('/capture', function (Request $request) use (&$capturedId) {
        $capturedId = $request->getRequestId();

        return response()->json(['ok' => true]);
    });

    $this->app['router']->getRoutes()->refreshNameLookups();
    $response = $this->get('/capture');

    expect($capturedId)->toBeString()->not->toBeEmpty();
    expect($capturedId)->toBe($response->headers->get('s-request-id'));
});

it('uses configured header name', function () {
    config()->set('distributed-tracing.header_name', 'x-custom-id');

    $response = $this->get('/test');

    $response->assertHeader('x-custom-id');
    $customId = $response->headers->get('x-custom-id');
    expect(Context::get('x-custom-id'))->toBe($customId);
});

it('does not break when sentry is not installed', function () {
    $response = $this->get('/test');

    expect($response->status())->toBe(200);
    expect($response->headers->has('s-request-id'))->toBeTrue();
});
