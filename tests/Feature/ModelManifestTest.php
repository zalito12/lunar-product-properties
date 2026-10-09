<?php

use Gongarce\ProductProps\Events\ProductPropertiesChanged;
use Gongarce\ProductProps\Events\ProductPropertiesChangeReason as Reason;
use Gongarce\ProductProps\Models\Contracts\PropertyValue as PropertyValueContract;
use Gongarce\ProductProps\Models\PropertyValue;
use Gongarce\ProductProps\Tests\Fixtures\CustomPropertyValue;
use Illuminate\Support\Facades\Event;
use Lunar\Facades\ModelManifest;
use Lunar\Models\Product;

beforeEach(function () {
    ModelManifest::replace(PropertyValueContract::class, CustomPropertyValue::class);
});

it('resolves the replaced property value model everywhere', function () {
    $property = createProperty();
    $value = createValue($property);
    $product = Product::factory()->create();
    $product->properties()->attach($value);

    expect(PropertyValue::modelClass())->toBe(CustomPropertyValue::class)
        ->and(PropertyValue::query()->first())->toBeInstanceOf(CustomPropertyValue::class)
        ->and($product->properties()->first())->toBeInstanceOf(CustomPropertyValue::class)
        ->and($property->values()->first())->toBeInstanceOf(CustomPropertyValue::class);
});

it('dispatches each event once for the replaced model', function () {
    $product = Product::factory()->create();
    $value = CustomPropertyValue::create([
        'property_id' => createProperty()->id,
        'label' => ['en' => 'Cotton'],
    ]);
    // Booting the base class too must not register duplicated listeners.
    new PropertyValue;
    $product->properties()->attach($value);
    fakePropertyEvents();

    $value->update(['label' => ['en' => 'Linen']]);
    $value->delete();

    Event::assertDispatchedTimes(ProductPropertiesChanged::class, 2);
    expect(propertyProductIds(Reason::ValueUpdated))->toBe([$product->id])
        ->and(propertyProductIds(Reason::ValueDeleted))->toBe([$product->id]);
});
