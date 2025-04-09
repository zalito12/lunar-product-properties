<?php

namespace Gongarce\ProductProps\Filament\Resources;

use Gongarce\ProductProps\Filament\Resources\ProductResource\Pages\ManageProductPropsPage;
use Gongarce\ProductProps\Filament\Resources\ProductResource\RelationManagers\PropertyValuesRelationManager;

class ProductPropsExtension extends \Lunar\Admin\Support\Extending\ResourceExtension
{

    // public function getRelations(array $managers): array
    // {
    //     return [
    //         ...$managers,
    //         PropertyValuesRelationManager::class,
    //     ];
    // }
    public function extendPages(array $pages): array
    {
        return [
            ...$pages,
            'properties' => ManageProductPropsPage::route('/{record}/properties'),
        ];
    }

    public function extendSubNavigation(array $nav): array
    {
        return [
            ...$nav,
            ManageProductPropsPage::class,
        ];
    }
}
