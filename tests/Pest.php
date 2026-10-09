<?php

use Gongarce\ProductProps\Events\ProductPropertiesChanged;
use Gongarce\ProductProps\Events\ProductPropertiesChangeReason;
use Gongarce\ProductProps\Models\Property;
use Gongarce\ProductProps\Models\PropertyValue;
use Gongarce\ProductProps\Tests\PrefixedTestCase;
use Gongarce\ProductProps\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

uses(TestCase::class)->in('Feature');
uses(PrefixedTestCase::class)->in('Prefixed');

function createProperty(array $attributes = []): Property
{
    return Property::create([
        'handle' => 'prop-'.Str::random(8),
        'label' => ['en' => 'Material'],
        ...$attributes,
    ]);
}

function createValue(?Property $property = null, array $attributes = []): PropertyValue
{
    return PropertyValue::create([
        'property_id' => ($property ?? createProperty())->id,
        'label' => ['en' => 'Cotton'],
        ...$attributes,
    ]);
}

/**
 * Only fake the plugin event: Eloquent model events must keep running.
 */
function fakePropertyEvents(): void
{
    Event::fake([ProductPropertiesChanged::class]);
}

/**
 * @return \Illuminate\Support\Collection<int, ProductPropertiesChanged>
 */
function propertyEvents(?ProductPropertiesChangeReason $reason = null)
{
    return Event::dispatched(ProductPropertiesChanged::class)
        ->map(fn (array $args) => $args[0])
        ->filter(fn (ProductPropertiesChanged $event) => $reason === null || $event->reason === $reason)
        ->values();
}

/**
 * Union of the product ids of every dispatched event, optionally filtered by reason.
 *
 * @return list<int>
 */
function propertyProductIds(?ProductPropertiesChangeReason $reason = null): array
{
    return ProductPropertiesChanged::normalizeIds(
        propertyEvents($reason)->flatMap(fn (ProductPropertiesChanged $event) => $event->productIds)
    );
}
