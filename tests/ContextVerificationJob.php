<?php

namespace Hypersender\DistributedTracing\Tests;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Context;

class ContextVerificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;

    /** @var array<string, mixed> */
    public static array $captured = [];

    public function handle(): void
    {
        self::$captured = [
            's-request-id' => Context::get('s-request-id'),
            'instance_id' => Context::get('instance_id'),
            'instance_name' => Context::get('instance_name'),
            'service_instance_id' => Context::get('service_instance_id'),
        ];
    }
}
