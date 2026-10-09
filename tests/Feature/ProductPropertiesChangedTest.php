<?php

use Gongarce\ProductProps\Events\ProductPropertiesChanged;
use Gongarce\ProductProps\Events\ProductPropertiesChangeReason as Reason;
use Gongarce\ProductProps\Exceptions\PropertyHasValuesException;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Lunar\Models\Product;

beforeEach(function () {
    $this->property = createProperty();
    $this->value = createValue($this->property);
    $this->productA = Product::factory()->create();
    $this->productB = Product::factory()->create();
});

describe('property values', function () {
    it('dispatches ValueUpdated with every associated product when the label changes', function () {
        $this->value->products()->attach([$this->productA->id, $this->productB->id]);
        fakePropertyEvents();

        $this->value->update(['label' => ['en' => 'Linen']]);

        $event = propertyEvents()->sole();
        expect($event->reason)->toBe(Reason::ValueUpdated)
            ->and($event->productIds)->toBe([$this->productA->id, $this->productB->id])
            ->and($event->propertyId)->toBe($this->property->id)
            ->and($event->propertyValueId)->toBe($this->value->id);
    });

    it('dispatches ValueUpdated when the value moves to another property', function () {
        $other = createProperty();
        $this->value->products()->attach($this->productA);
        fakePropertyEvents();

        $this->value->update(['property_id' => $other->id]);

        $event = propertyEvents()->sole();
        expect($event->reason)->toBe(Reason::ValueUpdated)
            ->and($event->productIds)->toBe([$this->productA->id])
            ->and($event->propertyId)->toBe($other->id);
    });

    it('does not dispatch when nothing rendered has changed', function () {
        $this->value->products()->attach($this->productA);
        fakePropertyEvents();

        $this->value->save();
        $this->value->update(['label' => $this->value->label]);
        $this->value->touch();

        Event::assertNotDispatched(ProductPropertiesChanged::class);
    });

    it('does not dispatch when creating a value', function () {
        fakePropertyEvents();

        createValue($this->property);

        Event::assertNotDispatched(ProductPropertiesChanged::class);
    });

    it('keeps every product id although the pivots are removed by cascade', function () {
        $this->value->products()->attach([$this->productA->id, $this->productB->id]);
        $valueId = $this->value->id;
        fakePropertyEvents();

        $this->value->delete();

        $this->assertDatabaseCount('lunar_product_property_value', 0);
        $event = propertyEvents()->sole();
        expect($event->reason)->toBe(Reason::ValueDeleted)
            ->and($event->productIds)->toBe([$this->productA->id, $this->productB->id])
            ->and($event->propertyId)->toBe($this->property->id)
            ->and($event->propertyValueId)->toBe($valueId);
    });
});

describe('properties', function () {
    it('dispatches PropertyUpdated with the products of every value when the label changes', function () {
        $second = createValue($this->property, ['label' => ['en' => 'Wool']]);
        $this->value->products()->attach([$this->productA->id, $this->productB->id]);
        $second->products()->attach($this->productA);
        createValue(createProperty())->products()->attach(Product::factory()->create());
        fakePropertyEvents();

        $this->property->update(['label' => ['en' => 'Fabric']]);

        $event = propertyEvents()->sole();
        expect($event->reason)->toBe(Reason::PropertyUpdated)
            ->and($event->productIds)->toBe([$this->productA->id, $this->productB->id])
            ->and($event->propertyId)->toBe($this->property->id)
            ->and($event->propertyValueId)->toBeNull();
    });

    it('dispatches PropertyUpdated when the handle changes', function () {
        $this->value->products()->attach($this->productA);
        fakePropertyEvents();

        $this->property->update(['handle' => 'fabric']);

        expect(propertyEvents(Reason::PropertyUpdated)->sole()->productIds)->toBe([$this->productA->id]);
    });

    it('does not dispatch when nothing rendered has changed', function () {
        $this->value->products()->attach($this->productA);
        fakePropertyEvents();

        $this->property->save();
        $this->property->update(['handle' => $this->property->handle]);
        $this->property->touch();

        Event::assertNotDispatched(ProductPropertiesChanged::class);
    });

    it('refuses to delete a property that still has values', function () {
        $this->value->products()->attach($this->productA);
        fakePropertyEvents();

        expect(fn () => $this->property->delete())->toThrow(PropertyHasValuesException::class);

        $this->assertModelExists($this->property);
        $this->assertModelExists($this->value);
        $this->assertDatabaseCount('lunar_product_property_value', 1);
        Event::assertNotDispatched(ProductPropertiesChanged::class);
    });

    it('deletes a property without values dispatching an empty PropertyDeleted', function () {
        $this->value->delete();
        fakePropertyEvents();

        $this->property->delete();

        $this->assertModelMissing($this->property);
        $event = propertyEvents()->sole();
        expect($event->reason)->toBe(Reason::PropertyDeleted)
            ->and($event->productIds)->toBe([])
            ->and($event->propertyId)->toBe($this->property->id);
    });
});

describe('associations', function () {
    it('dispatches ProductAttached when attaching from the product side', function () {
        fakePropertyEvents();

        $this->productA->properties()->attach($this->value, ['position' => 1]);

        $event = propertyEvents()->sole();
        expect($event->reason)->toBe(Reason::ProductAttached)
            ->and($event->productIds)->toBe([$this->productA->id])
            ->and($event->propertyId)->toBe($this->property->id)
            ->and($event->propertyValueId)->toBe($this->value->id);
    });

    it('dispatches the same contract when attaching from the value side', function () {
        fakePropertyEvents();

        $this->value->products()->attach($this->productA);

        $event = propertyEvents()->sole();
        expect($event->reason)->toBe(Reason::ProductAttached)
            ->and($event->productIds)->toBe([$this->productA->id])
            ->and($event->propertyId)->toBe($this->property->id)
            ->and($event->propertyValueId)->toBe($this->value->id);
    });

    it('dispatches ProductDetached from both sides', function () {
        $this->value->products()->attach([$this->productA->id, $this->productB->id]);
        fakePropertyEvents();

        $this->productA->properties()->detach($this->value);
        $this->value->products()->detach($this->productB);

        expect(propertyEvents(Reason::ProductDetached))->toHaveCount(2)
            ->and(propertyProductIds(Reason::ProductDetached))->toBe([$this->productA->id, $this->productB->id]);
    });

    it('dispatches ProductDetached when detaching every association at once', function () {
        $this->value->products()->attach([$this->productA->id, $this->productB->id]);
        fakePropertyEvents();

        $this->value->products()->detach();

        expect(propertyProductIds(Reason::ProductDetached))->toBe([$this->productA->id, $this->productB->id]);
        $this->assertDatabaseCount('lunar_product_property_value', 0);
    });

    it('covers added and removed associations on sync', function () {
        $this->value->products()->attach($this->productA);
        fakePropertyEvents();

        $this->value->products()->sync([$this->productB->id]);

        expect(propertyProductIds(Reason::ProductDetached))->toBe([$this->productA->id])
            ->and(propertyProductIds(Reason::ProductAttached))->toBe([$this->productB->id]);
    });

    it('covers sync from the product side', function () {
        $other = createValue($this->property);
        $this->productA->properties()->attach($this->value);
        fakePropertyEvents();

        $this->productA->properties()->sync([$other->id]);

        expect(propertyEvents(Reason::ProductDetached)->sole()->propertyValueId)->toBe($this->value->id)
            ->and(propertyEvents(Reason::ProductAttached)->sole()->propertyValueId)->toBe($other->id)
            ->and(propertyProductIds())->toBe([$this->productA->id]);
    });
});

describe('position', function () {
    it('dispatches PositionChanged when the pivot position changes', function () {
        $this->productA->properties()->attach($this->value, ['position' => 1]);
        fakePropertyEvents();

        $this->productA->properties()->updateExistingPivot($this->value->id, ['position' => 2]);

        $event = propertyEvents()->sole();
        expect($event->reason)->toBe(Reason::PositionChanged)
            ->and($event->productIds)->toBe([$this->productA->id])
            ->and($event->propertyValueId)->toBe($this->value->id);
    });

    it('dispatches PositionChanged when updating a loaded pivot', function () {
        $this->productA->properties()->attach($this->value, ['position' => 1]);
        fakePropertyEvents();

        $this->productA->properties()->first()->pivot->update(['position' => 5]);

        expect(propertyEvents(Reason::PositionChanged))->toHaveCount(1);
        $this->assertDatabaseHas('lunar_product_property_value', ['property_value_id' => $this->value->id, 'position' => 5]);
    });

    it('does not dispatch when the position is unchanged', function () {
        $this->productA->properties()->attach($this->value, ['position' => 1]);
        fakePropertyEvents();

        $this->productA->properties()->updateExistingPivot($this->value->id, ['position' => 1]);

        Event::assertNotDispatched(ProductPropertiesChanged::class);
    });
});

describe('payload', function () {
    it('normalizes ids to unique integers without nulls', function () {
        $event = new ProductPropertiesChanged(['3', 1, null, 3, '', 2], Reason::ProductAttached);

        expect($event->productIds)->toBe([1, 2, 3])
            ->and($event->propertyId)->toBeNull()
            ->and($event->propertyValueId)->toBeNull();
    });

    it('is dispatched after commit', function () {
        expect(new ProductPropertiesChanged([], Reason::ProductAttached))
            ->toBeInstanceOf(ShouldDispatchAfterCommit::class);
    });
});

describe('transactions', function () {
    it('does not publish anything when the transaction rolls back', function () {
        $received = [];
        Event::listen(ProductPropertiesChanged::class, function (ProductPropertiesChanged $event) use (&$received) {
            $received[] = $event;
        });

        try {
            DB::transaction(function () {
                $this->value->products()->attach($this->productA);
                $this->value->update(['label' => ['en' => 'Linen']]);
                $this->property->update(['label' => ['en' => 'Fabric']]);
                $this->value->delete();

                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException) {
        }

        expect($received)->toBe([]);
        $this->assertModelExists($this->value);
    });

    it('publishes only once the transaction commits', function () {
        $received = [];
        Event::listen(ProductPropertiesChanged::class, function (ProductPropertiesChanged $event) use (&$received) {
            $received[] = $event;
        });

        DB::transaction(function () use (&$received) {
            $this->value->products()->attach($this->productA);

            expect($received)->toBe([]);
        });

        expect($received)->toHaveCount(1)
            ->and($received[0]->productIds)->toBe([$this->productA->id]);
    });
});
