<?php

namespace Gongarce\ProductProps\Filament\Resources\PropertyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class PropertyValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('lunarpanel.product-props::property.relations.values.title_plural');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TranslatedTextColumn::make('label')
                    ->label(
                        __('lunarpanel.product-props::property.form.label.label')
                    )
                    ->searchable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label(
                        __('lunarpanel.product-props::property.relations.values.actions.create.label')
                    )
                    ->form([
                        TranslatedText::make('label')
                            ->label(__('lunarpanel.product-props::property.form.label.label'))
                            ->required(),
                    ])
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
