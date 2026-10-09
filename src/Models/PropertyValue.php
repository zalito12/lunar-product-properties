<?php

namespace Gongarce\ProductProps\Models;

use factories\PropertyValueFactory;
use Gongarce\ProductProps\Events\ProductPropertiesChanged;
use Gongarce\ProductProps\Events\ProductPropertiesChangeReason;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Base\Traits\Searchable;
use Lunar\Models\Product;

/**
 * @property int $id
 * @property string $label translatable label
 * @property string $answer translatable rich text
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class PropertyValue extends BaseModel implements Contracts\PropertyValue
{
    use HasFactory;
    use HasTranslations;
    use Searchable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_prop_values';

    /**
     * Define which attributes should be
     * protected from mass assignment.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'label' => AsCollection::class,
    ];

    /**
     * Products associated to this value, captured before deletion because the
     * pivot rows are removed by the database cascade. Not persisted.
     *
     * @var list<int>|null
     */
    protected ?array $productPropertiesDeletedProductIds = null;

    protected static function booted()
    {
        static::updated(function (self $value) {
            if (! $value->wasChanged(['label', 'property_id'])) {
                return;
            }

            ProductPropertiesChanged::dispatch(
                $value->affectedProductIds(),
                ProductPropertiesChangeReason::ValueUpdated,
                $value->property_id === null ? null : (int) $value->property_id,
                $value->getKey(),
            );
        });

        static::deleting(function (self $value) {
            $value->productPropertiesDeletedProductIds = $value->affectedProductIds();
        });

        static::deleted(function (self $value) {
            $productIds = $value->productPropertiesDeletedProductIds ?? [];
            $value->productPropertiesDeletedProductIds = null;

            ProductPropertiesChanged::dispatch(
                $productIds,
                ProductPropertiesChangeReason::ValueDeleted,
                $value->property_id === null ? null : (int) $value->property_id,
                $value->getKey(),
            );
        });
    }

    /**
     * Return the ids of every product this value is associated to.
     *
     * @return list<int>
     */
    public function affectedProductIds(): array
    {
        if (! $this->getKey()) {
            return [];
        }

        return ProductPropertyValue::productIdsForValues([$this->getKey()], $this->getConnectionName());
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return PropertyValueFactory::new();
    }

    /**
     * Return the property relationship.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::modelClass(), 'property_id');
    }

    /**
     * Return the products relationship.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::modelClass(), ProductPropertyValue::tableName())
            ->using(ProductPropertyValue::class);
    }
}
