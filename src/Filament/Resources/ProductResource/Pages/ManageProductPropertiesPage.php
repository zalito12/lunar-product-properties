<?php

namespace Gongarce\ProductProps\Filament\Resources\ProductResource\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Support\Facades\FilamentIcon;
use Filament\Tables;
use Filament\Tables\Table;
use Gongarce\ProductProps\Filament\Resources\QuestionResource;
use Gongarce\ProductProps\Models\PropertyValue;
use Gongarce\ProductProps\Models\Question;
use Lunar\Admin\Events\ProductCollectionsUpdated;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Pages\BaseManageRelatedRecords;
use Lunar\Admin\Support\Tables\Columns\TranslatedTextColumn;
use Lunar\Models\Collection;

class ManageProductPropertiesPage extends BaseManageRelatedRecords
{
    protected static string $resource = ProductResource::class;

    protected static string $relationship = 'property_values';

    public static function getNavigationIcon(): ?string
    {
        return FilamentIcon::resolve('lunar::product-props');
    }

    public function getTitle(): string
    {
        return __('lunarpanel.product-props::question.label_plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('lunarpanel.product-props::question.label_plural');
    }

    public function form(Form $form): Form
    {
        return QuestionResource::getDefaultForm($form);
    }

    //protected static ?string $recordTitleAttribute = 'text';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->recordTitle(fn(PropertyValue $record) => $record->translate('label'))
            ->columns(QuestionResource::getTableColumns())
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
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
