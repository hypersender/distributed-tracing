<?php

namespace Hypersender\DistributedTracing\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AddRequestId
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $headerName = config('distributed-tracing.header_name', 's-request-id');
        $requestId = (string) Str::orderedUuid();

        $request->headers->set($headerName, $requestId);
        $request->attributes->set($headerName, $requestId);

        if (class_exists(\Illuminate\Support\Facades\Context::class)) {
            \Illuminate\Support\Facades\Context::add($headerName, $requestId);
        }

        if (app()->has('sentry')) {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($headerName, $requestId): void {
                $scope->setTag($headerName, $requestId);
            });
        }

        /** @var Response $response */
        $response = $next($request);

        $response->headers->set($headerName, $requestId);

        return $response;
    }
}
