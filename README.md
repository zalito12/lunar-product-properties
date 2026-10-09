# Lunar Product Properties

Allows to create translatable properties (with values) and associate them to products.

TODO:
* Associate to variants, collections, brands, etc.

# Requirements

- LunarPHP `~1.4.0`

# Installation

Install via Composer

```
composer require gongarce/lunar-product-properties
```

Then register the plugin in your service provider

```php
use Lunar\Admin\Support\Facades\LunarPanel;
use Gongarce\ProductProps\ProductPropsPlugin;
// ...

public function register(): void
{
    LunarPanel::panel(function (Panel $panel) {
        return $panel->plugin(new ProductPropsPlugin());
    })->register();
    
    // ...
}
```

# Events

Whenever the properties rendered for one or more products may have changed, the plugin dispatches
`Gongarce\ProductProps\Events\ProductPropertiesChanged`. It implements `ShouldDispatchAfterCommit`, so it is
never published if the surrounding transaction rolls back.

```php
use Gongarce\ProductProps\Events\ProductPropertiesChanged;

Event::listen(function (ProductPropertiesChanged $event) {
    $event->productIds;      // list<int>: unique ids of every affected product
    $event->reason;          // ProductPropertiesChangeReason
    $event->propertyId;      // ?int, may point to an already deleted property
    $event->propertyValueId; // ?int, may point to an already deleted value
});
```

| Reason            | When                                                                         |
|-------------------|------------------------------------------------------------------------------|
| `PropertyUpdated` | `label` or `handle` of a property changes (products of all its values)       |
| `PropertyDeleted` | A property is deleted (always without values, so `productIds` is empty)      |
| `ValueUpdated`    | `label` or `property_id` of a value changes                                  |
| `ValueDeleted`    | A value is deleted (ids are captured before the cascade)                     |
| `ProductAttached` | A value is attached to a product (`attach`, `sync`...)                       |
| `ProductDetached` | A value is detached from a product (`detach`, `sync`...)                     |
| `PositionChanged` | The pivot `position` changes (e.g. reordering in the panel)                  |

Both sides of the relationship (`PropertyValue::products()` and `Product::properties()`) use the
`Gongarce\ProductProps\Models\ProductPropertyValue` pivot, so these events are dispatched both from the admin
panel and from your own Eloquent code. Mass updates or deletes through the query builder
(e.g. `PropertyValue::query()->delete()`) bypass Eloquent events and therefore don't dispatch it.

## Deleting properties

A property can't be deleted while it still has values: delete its values first (each one dispatches
`ValueDeleted`). Deleting it through Eloquent throws `Gongarce\ProductProps\Exceptions\PropertyHasValuesException`,
and the admin panel shows an error notification instead.

# Testing

```
composer test
```
