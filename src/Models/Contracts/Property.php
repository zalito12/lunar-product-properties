<?php

namespace Gongarce\ProductProps\Models\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface Property
{

    /**
     * Return the properties products relationship.
     */
    public function products(): BelongsToMany;
}
