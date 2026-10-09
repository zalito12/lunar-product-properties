<?php

namespace Gongarce\ProductProps\Exceptions;

use Gongarce\ProductProps\Models\Property;
use RuntimeException;

/**
 * Thrown when deleting a property that still has values.
 */
class PropertyHasValuesException extends RuntimeException
{
    public static function for(Property $property): static
    {
        return new static("Property [{$property->handle}] can't be deleted while it has values.");
    }
}
