<?php

namespace Gongarce\ProductProps\Filament\Resources\PropertyResource\Pages;

use Filament\Actions;
use Gongarce\ProductProps\Filament\Resources\PropertyResource;
use Lunar\Admin\Support\Pages\BaseEditRecord;

class EditProperty extends BaseEditRecord
{
    protected static string $resource = PropertyResource::class;

    protected function getDefaultHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
