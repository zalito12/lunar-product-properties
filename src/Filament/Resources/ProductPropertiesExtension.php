<?php

namespace Gongarce\ProductProps\Filament\Resources;

use Gongarce\ProductProps\Filament\Resources\ProductResource\Pages\ManageProductQuestionsPage;

class ProductQuestionsExtension extends \Lunar\Admin\Support\Extending\ResourceExtension
{

    public function extendPages(array $pages): array
    {
        return [
            ...$pages,
            'properties' => ManageProductQuestionsPage::route('/{record}/questions'),
        ];
    }

    public function extendSubNavigation(array $nav): array
    {
        return [
            ...$nav,
            ManageProductQuestionsPage::class,
        ];
    }
}
