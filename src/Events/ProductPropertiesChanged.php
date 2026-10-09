<?php

namespace Gongarce\ProductProps\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Dispatched (after commit) whenever the properties rendered for one or more products may have changed.
 */
final readonly class ProductPropertiesChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    /**
     * @var list<int>
     */
    public array $productIds;

    /**
     * @param  iterable<int|string|null>  $productIds
     */
    public function __construct(
        iterable $productIds,
        public ProductPropertiesChangeReason $reason,
        public ?int $propertyId = null,
        public ?int $propertyValueId = null,
    ) {
        $this->productIds = static::normalizeIds($productIds);
    }

    /**
     * @param  iterable<int|string|null>  $ids
     * @return list<int>
     */
    public static function normalizeIds(iterable $ids): array
    {
        $normalized = [];

        foreach ($ids as $id) {
            if ($id === null || $id === '') {
                continue;
            }

            $normalized[(int) $id] = (int) $id;
        }

        sort($normalized);

        return array_values($normalized);
    }
}
