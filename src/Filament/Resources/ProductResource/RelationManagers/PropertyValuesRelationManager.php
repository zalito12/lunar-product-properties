<?php

namespace Gongarce\ProductProps\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class PropertyValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'properties';

    /* public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel.product-props::question.relations.products.title_plural');
    } */

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel::user.plural_label');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title'),
            ]);
    }
}
