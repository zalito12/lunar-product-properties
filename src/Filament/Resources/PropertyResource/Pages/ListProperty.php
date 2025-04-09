<?php

namespace Gongarce\ProductProps\Filament\Resources\QuestionResource\Pages;

use Filament\Actions;
use Lunar\Admin\Support\Pages\BaseListRecords;
use Gongarce\ProductProps\Filament\Resources\PropertyResource;

class ListProperty extends BaseListRecords
{
    protected static string $resource = PropertyResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
