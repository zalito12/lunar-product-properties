<?php

use Filament\Facades\Filament;
use Gongarce\ProductProps\Filament\Resources\ProductResource\Pages\ManageProductPropsPage;
use Gongarce\ProductProps\Filament\Resources\PropertyResource\Pages\EditProperty;
use Gongarce\ProductProps\Filament\Resources\PropertyResource\RelationManagers\PropertyValuesRelationManager;
use Gongarce\ProductProps\Models\PropertyValue;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Livewire\Livewire;
use Lunar\Models\Product;

/*
 * Behaviour that existed before ProductPropertiesChanged was introduced and must keep working.
 */

it('exposes the same public relationships', function () {
    $property = createProperty();
    $value = createValue($property);
    $product = Product::factory()->create();

    expect($property->values())->toBeInstanceOf(HasMany::class)
        ->and($value->property())->toBeInstanceOf(BelongsTo::class)
        ->and($value->products())->toBeInstanceOf(BelongsToMany::class)
        ->and($value->products()->getTable())->toBe('lunar_product_property_value')
        ->and($product->properties())->toBeInstanceOf(BelongsToMany::class)
        ->and($product->properties()->getTable())->toBe('lunar_product_property_value')
        ->and($product->properties()->getPivotColumns())->toContain('position');
});

it('stores translated labels', function () {
    $property = createProperty(['label' => ['en' => 'Material']]);
    $value = createValue($property, ['label' => ['en' => 'Cotton']]);

    expect($property->refresh()->translate('label'))->toBe('Material')
        ->and($value->refresh()->translate('label'))->toBe('Cotton')
        ->and($value->property->is($property))->toBeTrue()
        ->and($property->values()->first()->is($value))->toBeTrue();
});

it('associates values to products from both sides', function () {
    $value = createValue();
    $product = Product::factory()->create();

    $product->properties()->attach($value, ['position' => 2]);

    expect($value->products()->first()->is($product))->toBeTrue()
        ->and($product->properties()->first()->is($value))->toBeTrue()
        ->and((int) $product->properties()->first()->pivot->position)->toBe(2);

    $value->products()->detach($product);

    expect($product->properties()->count())->toBe(0);
});

it('orders product properties by pivot position', function () {
    $first = createValue();
    $second = createValue();
    $product = Product::factory()->create();
    $product->properties()->attach($first, ['position' => 2]);
    $product->properties()->attach($second, ['position' => 1]);

    expect($product->properties()->pluck('property_value_id')->all())->toBe([$second->id, $first->id]);
});

it('removes associations when a value is deleted', function () {
    $value = createValue();
    Product::factory()->create()->properties()->attach($value);

    $value->delete();

    $this->assertDatabaseCount('lunar_product_property_value', 0);
});

it('can not delete a property that still has values', function () {
    $property = createProperty();
    createValue($property);

    expect(fn () => $property->delete())->toThrow(Exception::class);

    $this->assertModelExists($property);
});

it('keeps the Filament pages working', function () {
    Filament::setCurrentPanel(Filament::getPanel('lunar'));
    $this->asStaff();
    $property = createProperty();
    $value = createValue($property);
    $second = createValue($property);
    $product = Product::factory()->create();

    Livewire::test(ManageProductPropsPage::class, ['record' => $product->getRouteKey()])
        ->callTableAction('attach', data: ['recordId' => $value->id])
        ->callTableAction('attach', data: ['recordId' => $second->id])
        ->assertCanSeeTableRecords([$value, $second])
        ->call('reorderTable', [(string) $second->id, (string) $value->id])
        ->callTableAction('detach', $value)
        ->assertHasNoTableActionErrors();

    expect($product->properties()->pluck('property_value_id')->all())->toBe([$second->id]);

    Livewire::test(PropertyValuesRelationManager::class, ['ownerRecord' => $property, 'pageClass' => EditProperty::class])
        ->assertCanSeeTableRecords([$value, $second])
        ->callTableAction('create', data: ['label' => ['en' => 'Wool']])
        ->assertHasNoTableActionErrors();

    expect(PropertyValue::query()->where('property_id', $property->id)->count())->toBe(3);
});
