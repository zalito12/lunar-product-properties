<?php

namespace Gongarce\ProductProps\Filament\Resources\ProductResource\Pages;

use Filament\Actions;
use Gongarce\ProductProps\Filament\Resources\ProductResource\RelationManagers\PropertyValuesRelationManager;
use Lunar\Admin\Support\Extending\EditPageExtension;
use Lunar\Admin\Filament\Widgets;

class EditWithProperties extends EditPageExtension
{
    public function heading($title, $record): string
    {
        return $title . ' - Example';
    }
    public function relationManagers(array $managers): array
    {
        return [
            ...$managers,
            PropertyValuesRelationManager::class
        ];
    }
    public function getRelationManagers(): array
    {
        return [
            PropertyValuesRelationManager::class
        ];
    }
}
