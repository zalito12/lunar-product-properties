<?php

namespace Gongarce\ProductProps\Filament\Resources\ProductResource\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Gongarce\ProductProps\Models\PropertyValue;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;

class ManageProductPropsPage extends BaseManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'properties';

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-props');
    }

    public function getTitle(): string
    {
        return __('lunarpanel.product-props::property.label_plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel.product-props::property.label_plural');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('property_id')
                    ->relationship(name: 'property', titleAttribute: 'handle')
                    ->label(__('lunarpanel.product-props::property.form.text.label')),
                TranslatedText::make('label')
                    ->label(__('lunarpanel.product-props::property-value.form.text.label'))
                    ->required()
                    ->autofocus(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('property.handle')
                ->label(
                    __('lunarpanel.product-props::property.table.text.handle')
                ),
            TranslatedTextColumn::make('label')
                ->label(
                    __('lunarpanel.product-props::property-value.table.text.label')
                )
                ->searchable(),
        ])
            ->recordTitleAttribute('label')
            ->recordTitle(fn(PropertyValue $record) => $record->translate('label'))
            ->reorderable('position')
            ->defaultSort('position')
            ->headerActions([
                Tables\Actions\AttachAction::make(),
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DetachAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
