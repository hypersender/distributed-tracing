<?php

namespace Hypersender\DistributedTracing;

use Illuminate\Database\Eloquent\Model;

class InjectInstanceContext
{
    /**
     * Inject instance context into Laravel Context and Sentry.
     *
     * All fields accessed are in-memory — zero database queries:
     * - $instance->getKey() — primary key, always loaded
     * - $instance->getAttribute('name') — column, always loaded
     * - $instance->getAttribute('service_type') — enum cast on column, ->name is a PHP property
     *
     * Wrapped in try/catch — a failure in tracing will never break the request.
     */
    public static function for(Model $instance): void
    {
        try {
            $instanceId = $instance->getKey();
            $instanceName = $instance->getAttribute('name');
            $serviceType = $instance->getAttribute('service_type');

            $serviceInstanceId = $serviceType instanceof \BackedEnum
                ? sprintf('%s-%s', $serviceType->name, $instanceId)
                : (string) $instanceId;

            if (class_exists(\Illuminate\Support\Facades\Context::class)) {
                \Illuminate\Support\Facades\Context::add('instance_id', $instanceId);
                \Illuminate\Support\Facades\Context::add('instance_name', $instanceName);
                \Illuminate\Support\Facades\Context::add('service_instance_id', $serviceInstanceId);
            }

            if (app()->has('sentry')) {
                \Sentry\configureScope(function (\Sentry\State\Scope $scope) use ($instanceId, $instanceName, $serviceInstanceId): void {
                    $scope->setTag('instance_id', (string) $instanceId);
                    $scope->setTag('instance_name', (string) $instanceName);
                    $scope->setTag('service_instance_id', $serviceInstanceId);
                });
            }
        } catch (\Throwable) {
            // Never let tracing fail a request.
        }
    }
}
