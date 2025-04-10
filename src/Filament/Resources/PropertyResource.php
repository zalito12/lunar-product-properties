<?php

namespace Gongarce\ProductProps\Filament\Resources;

use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Lunar\Admin\Support\Forms\Components\TranslatedText;
use Lunar\Admin\Support\Resources\BaseResource;
use Gongarce\ProductProps\Filament\Resources\PropertyResource\Pages;
use Gongarce\ProductProps\Filament\Resources\PropertyResource\RelationManagers\PropertyValuesRelationManager;
use Gongarce\ProductProps\Models\Property;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;

class PropertyResource extends BaseResource
{
    protected static ?string $model = Property::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';  // TODO: remove me in Filament 3.1

    protected static ?int $navigationSort = 4;

    public static function getLabel(): string
    {
        return __('lunarpanel.product-props::property.label');
    }

    public static function getPluralLabel(): string
    {
        return __('lunarpanel.product-props::property.label_plural');
    }

    public static function getNavigationParentItem(): ?string
    {
        return __('lunarpanel::product.plural_label');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('lunarpanel::global.sections.catalog');
    }

    public static function getDefaultForm(Form $form): Form
    {
        return $form
            ->schema(
                static::getMainFormComponents(),
            )
            ->columns(1);
    }

    protected static function getMainFormComponents(): array
    {
        return [
            static::getHandleFormComponent(),
            static::getLabelFormComponent(),
        ];
    }

    public static function getHandleFormComponent(): Component
    {
        return
            TextInput::make('handle')
            ->label(__('lunarpanel.product-props::property.form.handle.label'))
            ->required()
            ->autofocus();
    }

    public static function getLabelFormComponent(): Component
    {
        return
            TranslatedText::make('label')
            ->label(__('lunarpanel.product-props::property.form.label.label'))
            ->required();
    }

    public static function getDefaultTable(Table $table): Table
    {
        return $table
            ->columns(static::getTableColumns())
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getTableColumns(): array
    {
        return [
            TranslatedTextColumn::make('label')
                ->label(
                    __('lunarpanel.product-props::property.form.label.label')
                )
                ->searchable(),
        ];
    }

    public static function getRelations(): array
    {
        return [
            PropertyValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProperty::route('/'),
            'edit' => Pages\EditProperty::route('/{record}/edit'),
        ];
    }
}
