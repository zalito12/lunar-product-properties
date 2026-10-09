<?php

use Gongarce\ProductProps\Models\ProductPropertyValue;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lunar\Models\Product;

it('uses the plugin pivot from both sides of the relationship', function () {
    $value = createValue();
    $product = Product::factory()->create();

    expect($value->products())->toBeInstanceOf(BelongsToMany::class)
        ->and($value->products()->getPivotClass())->toBe(ProductPropertyValue::class)
        ->and($product->properties())->toBeInstanceOf(BelongsToMany::class)
        ->and($product->properties()->getPivotClass())->toBe(ProductPropertyValue::class);
});

it('exposes the plugin pivot on loaded relations', function () {
    $value = createValue();
    $product = Product::factory()->create();
    $product->properties()->attach($value, ['position' => 4]);

    $pivot = $product->properties()->first()->pivot;

    expect($pivot)->toBeInstanceOf(ProductPropertyValue::class)
        ->and((int) $pivot->position)->toBe(4)
        ->and($value->products()->first()->pivot)->toBeInstanceOf(ProductPropertyValue::class);
});
