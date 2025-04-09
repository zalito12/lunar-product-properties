<?php

namespace Gongarce\ProductProps;

use Gongarce\ProductProps\Models\PropertyValue;
use Illuminate\Support\ServiceProvider;
use Lunar\Facades\ModelManifest;
use Lunar\Models\Product;

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

        Product::resolveRelationUsing('properties', function (Product $product) {
            $prefix = config('lunar.database.table_prefix');
            return $product->belongsToMany(PropertyValue::class, "{$prefix}product_property_value")
                ->withPivot('position')->orderByPivot('position');
        });

        ModelManifest::addDirectory(
            __DIR__ . '/Models'
        );
    }
}
