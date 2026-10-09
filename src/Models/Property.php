<?php

namespace Gongarce\ProductProps\Models;

use factories\PropertyFactory;
use Gongarce\ProductProps\Events\ProductPropertiesChanged;
use Gongarce\ProductProps\Events\ProductPropertiesChangeReason;
use Gongarce\ProductProps\Exceptions\PropertyHasValuesException;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Lunar\Base\BaseModel;
use Lunar\Base\Traits\HasTranslations;
use Lunar\Base\Traits\Searchable;
use Lunar\Models\Product;

/**
 * @property int $id
 * @property string $label translatable label
 * @property ?string $handle
 * @property ?\Illuminate\Support\Carbon $created_at
 * @property ?\Illuminate\Support\Carbon $updated_at
 */
class Property extends BaseModel
{
    use HasFactory;
    use HasTranslations;
    use Searchable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_props';

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

    protected static function booted()
    {
        static::updated(function (self $property) {
            if (! $property->wasChanged(['label', 'handle'])) {
                return;
            }

            ProductPropertiesChanged::dispatch(
                $property->affectedProductIds(),
                ProductPropertiesChangeReason::PropertyUpdated,
                $property->getKey(),
            );
        });

        // Properties can only be deleted once they have no values, so no
        // product can lose a rendered property without a ValueDeleted event.
        static::deleting(function (self $property) {
            if ($property->hasValues()) {
                throw PropertyHasValuesException::for($property);
            }
        });

        static::deleted(function (self $property) {
            ProductPropertiesChanged::dispatch(
                [],
                ProductPropertiesChangeReason::PropertyDeleted,
                $property->getKey(),
            );
        });
    }

    public function hasValues(): bool
    {
        return $this->values()->exists();
    }

    /**
     * Return the ids of every product associated to any of this property's values.
     *
     * @return list<int>
     */
    public function affectedProductIds(): array
    {
        if (! $this->getKey()) {
            return [];
        }

        return ProductPropertyValue::productIdsForValues(
            $this->values()->pluck($this->values()->getRelated()->getQualifiedKeyName()),
            $this->getConnectionName(),
        );
    }

    /**
     * Return a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return PropertyFactory::new();
    }

    /**
     * Return the property values relationship.
     */
    public function values(): HasMany
    {
        return $this->hasMany(PropertyValue::modelClass(), 'property_id');
    }
}
