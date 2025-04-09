<?php

namespace Gongarce\ProductProps;

use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\Support\Facades\FilamentIcon;
use Gongarce\ProductProps\Filament\Resources\ProductPropertysExtension;
use Gongarce\ProductProps\Filament\Resources\ProductResource\MyProductResourceExtension;
use Gongarce\ProductProps\Filament\Resources\ProductResource\Pages\MyPage;
use Gongarce\ProductProps\Filament\Resources\ProductResource\Pages\MyRelationExtension;
use Gongarce\ProductProps\Filament\Resources\ShippingExclusionListResource;
use Gongarce\ProductProps\Filament\Resources\PropertyResource;
use Gongarce\ProductProps\Filament\Resources\ShippingZoneResource;
use Lunar\Admin\Support\Facades\LunarPanel;

class ProductPropsPlugin implements Plugin
{
    public function getId(): string
    {
        return 'product-properties';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }

    public function register(Panel $panel): void
    {
        if (! config('lunar.product-properties.enabled')) {
            return;
        }

        $panel->resources([
            PropertyResource::class,
        ]);

        LunarPanel::extensions([
            \Lunar\Admin\Filament\Resources\ProductResource::class => ProductPropertysExtension::class,
        ]);

        FilamentIcon::register([
            'lunar::product-properties' => 'lucide-list',
        ]);
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public function panel(Panel $panel): Panel
    {
        return $panel;
    }

    // ...
}
