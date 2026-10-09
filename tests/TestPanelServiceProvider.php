<?php

namespace Gongarce\ProductProps\Tests;

use Gongarce\ProductProps\ProductPropsPlugin;
use Illuminate\Support\ServiceProvider;
use Lunar\Admin\Support\Facades\LunarPanel;

class TestPanelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        LunarPanel::panel(fn ($panel) => $panel->plugin(new ProductPropsPlugin))->register();
    }
}
