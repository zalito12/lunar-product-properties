<?php

namespace Gongarce\ProductProps\Events;

enum ProductPropertiesChangeReason: string
{
    case PropertyUpdated = 'property_updated';
    case PropertyDeleted = 'property_deleted';
    case ValueUpdated = 'value_updated';
    case ValueDeleted = 'value_deleted';
    case ProductAttached = 'product_attached';
    case ProductDetached = 'product_detached';
    case PositionChanged = 'position_changed';
}
