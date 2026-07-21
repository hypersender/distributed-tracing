<?php

use Hypersender\DistributedTracing\InjectInstanceContext;
use Hypersender\DistributedTracing\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Context;

enum TestServiceType: int
{
    case WHATSAPP = 1;
    case SMS = 2;
}

beforeEach(function () {
    Context::flush();
});

it('sets instance_id, instance_name, and service_instance_id in Context', function () {
    $instance = new class extends Model
    {
        protected $attributes = [
            'id' => 42,
            'name' => 'test-instance',
            'service_type' => null,
        ];
    };

    InjectInstanceContext::for($instance);

    expect(Context::get('instance_id'))->toBe(42);
    expect(Context::get('instance_name'))->toBe('test-instance');
    expect(Context::get('service_instance_id'))->toBe('42');
});

it('generates service_instance_id with service type prefix when BackedEnum', function () {
    $serviceType = TestServiceType::WHATSAPP;

    $instance = new class extends Model
    {
        protected $attributes = [
            'id' => 739,
            'name' => 'my-instance',
        ];
    };

    $instance->setRawAttributes([
        'id' => 739,
        'name' => 'my-instance',
        'service_type' => $serviceType,
    ]);

    InjectInstanceContext::for($instance);

    expect(Context::get('service_instance_id'))->toBe('WHATSAPP-739');
});

it('never throws an exception, even with invalid instance', function () {
    $instance = new class extends Model
    {
        protected $attributes = [];
    };

    InjectInstanceContext::for($instance);

    expect(true)->toBeTrue();
});

it('flush clears context between tests', function () {
    Context::add('instance_id', 999);

    expect(Context::get('instance_id'))->toBe(999);

    Context::flush();

    expect(Context::get('instance_id'))->toBeNull();
});
