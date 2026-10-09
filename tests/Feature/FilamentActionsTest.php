<?php

use Filament\Facades\Filament;
use Gongarce\ProductProps\Events\ProductPropertiesChanged;
use Gongarce\ProductProps\Events\ProductPropertiesChangeReason as Reason;
use Gongarce\ProductProps\Filament\Resources\ProductResource\Pages\ManageProductPropsPage;
use Gongarce\ProductProps\Filament\Resources\PropertyResource\Pages\EditProperty;
use Gongarce\ProductProps\Filament\Resources\PropertyResource\Pages\ListProperty;
use Gongarce\ProductProps\Filament\Resources\PropertyResource\RelationManagers\PropertyValuesRelationManager;
use Gongarce\ProductProps\Models\PropertyValue;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Lunar\Models\Product;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('lunar'));
    $this->asStaff();

    $this->product = Product::factory()->create();
    $this->property = createProperty();
    $this->value = createValue($this->property);
});

function productPropsPage(Product $product)
{
    return Livewire::test(ManageProductPropsPage::class, ['record' => $product->getRouteKey()]);
}

describe('product properties page', function () {
    it('creates a value attached to the product', function () {
        fakePropertyEvents();

        productPropsPage($this->product)
            ->callTableAction('create', data: [
                'property_id' => $this->property->id,
                'label' => ['en' => 'Linen'],
            ])
            ->assertHasNoTableActionErrors();

        $created = PropertyValue::query()->latest('id')->first();
        expect($created->translate('label'))->toBe('Linen')
            ->and($this->product->properties()->pluck('property_value_id')->all())->toBe([$created->id]);
        $event = propertyEvents()->sole();
        expect($event->reason)->toBe(Reason::ProductAttached)
            ->and($event->productIds)->toBe([$this->product->id])
            ->and($event->propertyValueId)->toBe($created->id);
    });

    it('attaches a value', function () {
        fakePropertyEvents();

        productPropsPage($this->product)
            ->callTableAction('attach', data: ['recordId' => $this->value->id])
            ->assertHasNoTableActionErrors();

        expect($this->product->properties()->pluck('property_value_id')->all())->toBe([$this->value->id])
            ->and(propertyEvents(Reason::ProductAttached)->sole()->productIds)->toBe([$this->product->id]);
    });

    it('detaches a value', function () {
        $this->product->properties()->attach($this->value);
        fakePropertyEvents();

        productPropsPage($this->product)
            ->callTableAction('detach', $this->value)
            ->assertHasNoTableActionErrors();

        expect($this->product->properties()->count())->toBe(0)
            ->and(propertyEvents(Reason::ProductDetached)->sole()->productIds)->toBe([$this->product->id]);
    });

    it('bulk detaches values', function () {
        $second = createValue($this->property);
        $this->product->properties()->attach([$this->value->id, $second->id]);
        fakePropertyEvents();

        productPropsPage($this->product)
            ->callTableBulkAction('detach', [$this->value, $second])
            ->assertHasNoTableActionErrors();

        expect($this->product->properties()->count())->toBe(0)
            ->and(propertyEvents(Reason::ProductDetached))->toHaveCount(2)
            ->and(propertyProductIds(Reason::ProductDetached))->toBe([$this->product->id]);
    });

    it('edits a value', function () {
        $other = Product::factory()->create();
        $this->product->properties()->attach($this->value);
        $other->properties()->attach($this->value);
        fakePropertyEvents();

        productPropsPage($this->product)
            ->callTableAction('edit', $this->value, data: [
                'property_id' => $this->property->id,
                'label' => ['en' => 'Linen'],
            ])
            ->assertHasNoTableActionErrors();

        expect($this->value->refresh()->translate('label'))->toBe('Linen')
            ->and(propertyEvents(Reason::ValueUpdated)->sole()->productIds)->toBe([$this->product->id, $other->id]);
    });

    it('deletes a value', function () {
        $other = Product::factory()->create();
        $this->product->properties()->attach($this->value);
        $other->properties()->attach($this->value);
        fakePropertyEvents();

        productPropsPage($this->product)
            ->callTableAction('delete', $this->value)
            ->assertHasNoTableActionErrors();

        $this->assertModelMissing($this->value);
        expect(propertyEvents(Reason::ValueDeleted)->sole()->productIds)->toBe([$this->product->id, $other->id]);
    });

    it('bulk deletes values', function () {
        $second = createValue($this->property);
        $this->product->properties()->attach([$this->value->id, $second->id]);
        fakePropertyEvents();

        productPropsPage($this->product)
            ->callTableBulkAction('delete', [$this->value, $second])
            ->assertHasNoTableActionErrors();

        expect(propertyEvents(Reason::ValueDeleted))->toHaveCount(2)
            ->and(propertyProductIds(Reason::ValueDeleted))->toBe([$this->product->id]);
    });

    it('reorders values', function () {
        $second = createValue($this->property);
        $this->product->properties()->attach($this->value, ['position' => 1]);
        $this->product->properties()->attach($second, ['position' => 2]);
        fakePropertyEvents();

        productPropsPage($this->product)
            ->call('reorderTable', [(string) $second->id, (string) $this->value->id]);

        expect($this->product->properties()->pluck('property_value_id')->all())->toBe([$second->id, $this->value->id])
            ->and(propertyEvents(Reason::PositionChanged))->toHaveCount(2)
            ->and(propertyProductIds(Reason::PositionChanged))->toBe([$this->product->id]);
    });
});

describe('property resource', function () {
    it('updates a property from the edit page', function () {
        $this->product->properties()->attach($this->value);
        fakePropertyEvents();

        Livewire::test(EditProperty::class, ['record' => $this->property->getRouteKey()])
            ->fillForm(['label' => ['en' => 'Fabric']])
            ->call('save')
            ->assertHasNoFormErrors();

        expect(propertyEvents(Reason::PropertyUpdated)->sole()->productIds)->toBe([$this->product->id]);
    });

    it('refuses to delete a property with values from the edit page', function () {
        fakePropertyEvents();

        Livewire::test(EditProperty::class, ['record' => $this->property->getRouteKey()])
            ->callAction('delete')
            ->assertNotified(__('lunarpanel.product-props::property.notifications.has_values.title'));

        $this->assertModelExists($this->property);
        Event::assertNotDispatched(ProductPropertiesChanged::class);
    });

    it('refuses to delete a property with values from the list', function () {
        Livewire::test(ListProperty::class)
            ->callTableAction('delete', $this->property)
            ->assertNotified(__('lunarpanel.product-props::property.notifications.has_values.title'));

        $this->assertModelExists($this->property);
    });

    it('refuses to bulk delete when any property has values', function () {
        $empty = createProperty();

        Livewire::test(ListProperty::class)
            ->callTableBulkAction('delete', [$this->property, $empty])
            ->assertNotified(__('lunarpanel.product-props::property.notifications.has_values.title'));

        $this->assertModelExists($this->property);
        $this->assertModelExists($empty);
    });

    it('deletes a property without values', function () {
        $empty = createProperty();
        fakePropertyEvents();

        Livewire::test(ListProperty::class)
            ->callTableAction('delete', $empty)
            ->assertHasNoTableActionErrors();

        $this->assertModelMissing($empty);
        expect(propertyEvents(Reason::PropertyDeleted)->sole()->propertyId)->toBe($empty->id);
    });
});

describe('property values relation manager', function () {
    function valuesManager($property)
    {
        return Livewire::test(PropertyValuesRelationManager::class, ['ownerRecord' => $property, 'pageClass' => EditProperty::class]);
    }

    it('creates a value without dispatching events', function () {
        fakePropertyEvents();

        valuesManager($this->property)
            ->callTableAction('create', data: ['label' => ['en' => 'Wool']])
            ->assertHasNoTableActionErrors();

        expect($this->property->values()->count())->toBe(2);
        Event::assertNotDispatched(ProductPropertiesChanged::class);
    });

    it('deletes a value', function () {
        $this->product->properties()->attach($this->value);
        fakePropertyEvents();

        valuesManager($this->property)
            ->callTableAction('delete', $this->value)
            ->assertHasNoTableActionErrors();

        expect(propertyEvents(Reason::ValueDeleted)->sole()->productIds)->toBe([$this->product->id]);
    });

    it('bulk deletes values', function () {
        $other = Product::factory()->create();
        $second = createValue($this->property);
        $this->product->properties()->attach($this->value);
        $other->properties()->attach($second);
        fakePropertyEvents();

        valuesManager($this->property)
            ->callTableBulkAction('delete', [$this->value, $second])
            ->assertHasNoTableActionErrors();

        expect(propertyProductIds(Reason::ValueDeleted))->toBe([$this->product->id, $other->id]);
    });
});
