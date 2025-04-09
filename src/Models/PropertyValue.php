<?php

namespace Gongarce\ProductProps\Models;

use factories\PropertyValueFactory;
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

    protected static function booted() {}

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
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * Return the products relationship.
     */
    public function products(): BelongsToMany
    {
        $prefix = config('lunar.database.table_prefix');
        return $this->belongsToMany(Product::class, "{$prefix}product_property_value");
    }
}
