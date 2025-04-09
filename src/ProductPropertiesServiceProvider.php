<?php

namespace Gongarce\ProductProps;

use Filament\Panel;
use Gongarce\ProductProps\Filament\Resources\ProductQuestionsExtension;
use Gongarce\ProductProps\Filament\Resources\ProductResource\MyProductResourceExtension;
use Gongarce\ProductProps\Filament\Resources\ProductResource\Pages\ManageProductQuestionsPage;
use Gongarce\ProductProps\Models\Question;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Filament\Resources\ProductResource;
use Lunar\Admin\Support\Facades\LunarPanel;
use Lunar\Facades\ModelManifest;
use Gongarce\ProductProps\Models\ShippingExclusion;
use Gongarce\ProductProps\Models\ShippingExclusionList;
use Gongarce\ProductProps\Models\ShippingRate;
use Gongarce\ProductProps\Models\ShippingZone;
use Gongarce\ProductProps\Models\ShippingZonePostcode;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class ProductPropsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/product-properties.php', 'lunar.product-properties');
    }

    public function boot()
    {
        if (! config('lunar.product-properties.enabled')) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'lunarpanel.product-properties');

        if (! config('lunar.database.disable_migrations', false)) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'product-properties');

        /* Product::resolveRelationUsing('questions', function (Product $product) {
            $prefix = config('lunar.database.table_prefix');
            return $product->morphToMany(Question::class, 'questionable', "{$prefix}questionable")
                ->withPivot('position');
        }); */

        ModelManifest::addDirectory(
            __DIR__ . '/Models'
        );

        // Relation::morphMap([
        //     'property' => Property::modelClass(),
        // ]);
    }
}
