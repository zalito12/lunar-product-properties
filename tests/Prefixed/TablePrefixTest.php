<?php

use Gongarce\ProductProps\Events\ProductPropertiesChangeReason as Reason;
use Lunar\Models\Product;

it('works with a custom lunar table prefix', function () {
    $property = createProperty();
    $value = createValue($property);
    $product = Product::factory()->create();
    fakePropertyEvents();

    $product->properties()->attach($value, ['position' => 1]);
    $product->properties()->updateExistingPivot($value->id, ['position' => 2]);
    $value->update(['label' => ['en' => 'Linen']]);
    $property->update(['label' => ['en' => 'Fabric']]);
    $value->delete();
    $property->delete();

    $this->assertDatabaseCount('shop_product_property_value', 0);
    expect($value->getTable())->toBe('shop_product_prop_values')
        ->and($property->getTable())->toBe('shop_product_props');

    foreach ([Reason::ProductAttached, Reason::PositionChanged, Reason::ValueUpdated, Reason::PropertyUpdated, Reason::ValueDeleted] as $reason) {
        expect(propertyProductIds($reason))->toBe([$product->id]);
    }

    expect(propertyEvents(Reason::PropertyDeleted))->toHaveCount(1);
});
