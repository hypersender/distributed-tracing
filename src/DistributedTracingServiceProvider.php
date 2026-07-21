<?php

namespace Hypersender\DistributedTracing;

use Hypersender\DistributedTracing\Middleware\AddRequestId;
use Illuminate\Contracts\Http\Kernel;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DistributedTracingServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('distributed-tracing')
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        $this->registerMiddleware();
        $this->registerRequestMacro();
    }

    private function registerMiddleware(): void
    {
        /** @var \Illuminate\Foundation\Http\Kernel $kernel */
        $kernel = $this->app->make(Kernel::class);

        if (method_exists($kernel, 'prependMiddleware')) {
            $kernel->prependMiddleware(AddRequestId::class);

            return;
        }

        $kernel->pushMiddleware(AddRequestId::class);
    }

    private function registerRequestMacro(): void
    {
        \Illuminate\Http\Request::macro('getRequestId', function (): ?string {
            $headerName = config('distributed-tracing.header_name', 's-request-id');

            if (class_exists(\Illuminate\Support\Facades\Context::class)) {
                $id = \Illuminate\Support\Facades\Context::get($headerName);

                if ($id !== null) {
                    return $id;
                }
            }

            /** @var \Illuminate\Http\Request $this */
            return $this->attributes->get($headerName);
        });
    }
}
