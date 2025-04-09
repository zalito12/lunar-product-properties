<?php

namespace Gongarce\ProductProps\Models;

use factories\PropertyFactory;
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
        /*static::deleting(function (self $shippingMethod) {
            DB::beginTransaction();
            $shippingMethod->customerGroups()->detach();
            $shippingMethod->shippingRates()->delete();
            DB::commit();
        });*/
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
        return $this->hasMany(PropertyValue::class);
    }
}
