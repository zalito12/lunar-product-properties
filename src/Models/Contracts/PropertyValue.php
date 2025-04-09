<?php

namespace Gongarce\ProductProps\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface PropertyValue
{

    /**
     * Return the properties relationship.
     */
    public function property(): BelongsTo;

    /**
     * Return the values products relationship.
     */
    public function products(): BelongsToMany;
}
