<?php

namespace Gongarce\ProductProps\Models;

use Gongarce\ProductProps\Events\ProductPropertiesChanged;
use Gongarce\ProductProps\Events\ProductPropertiesChangeReason;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot between property values and products.
 *
 * Both sides of the relationship use this pivot, so attach, detach, sync and
 * pivot updates all end up dispatching ProductPropertiesChanged.
 *
 * @property int $property_value_id
 * @property int $product_id
 * @property int $position
 */
class ProductPropertyValue extends Pivot
{
    protected static function booted(): void
    {
        static::created(function (self $pivot) {
            $pivot->dispatchProductPropertiesChanged(ProductPropertiesChangeReason::ProductAttached);
        });

        static::deleted(function (self $pivot) {
            $pivot->dispatchProductPropertiesChanged(ProductPropertiesChangeReason::ProductDetached);
        });

        static::updated(function (self $pivot) {
            if ($pivot->wasChanged('position')) {
                $pivot->dispatchProductPropertiesChanged(ProductPropertiesChangeReason::PositionChanged);
            }
        });
    }

    public function getTable()
    {
        return $this->table ?? static::tableName();
    }

    public static function tableName(): string
    {
        return config('lunar.database.table_prefix').'product_property_value';
    }

    /**
     * Return the ids of the products associated to the given property values.
     *
     * @param  iterable<int>  $propertyValueIds
     * @return list<int>
     */
    public static function productIdsForValues(iterable $propertyValueIds, ?string $connection = null): array
    {
        $propertyValueIds = collect($propertyValueIds)->all();

        if (empty($propertyValueIds)) {
            return [];
        }

        return ProductPropertiesChanged::normalizeIds(
            (new static)
                ->setConnection($connection)
                ->newQuery()
                ->toBase()
                ->whereIn('property_value_id', $propertyValueIds)
                ->pluck('product_id')
        );
    }

    protected function dispatchProductPropertiesChanged(ProductPropertiesChangeReason $reason): void
    {
        $propertyValueId = $this->getAttribute('property_value_id');
        $propertyValueClass = PropertyValue::modelClass();

        $propertyId = $propertyValueId === null ? null : $propertyValueClass::query()
            ->whereKey($propertyValueId)
            ->value('property_id');

        ProductPropertiesChanged::dispatch(
            [$this->getAttribute('product_id')],
            $reason,
            $propertyId === null ? null : (int) $propertyId,
            $propertyValueId === null ? null : (int) $propertyValueId,
        );
    }
}
