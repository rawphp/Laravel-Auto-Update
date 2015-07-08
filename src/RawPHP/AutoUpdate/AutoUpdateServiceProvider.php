<?php

namespace RawPHP\AutoUpdate;

use Illuminate\Support\ServiceProvider;

/**
 * Class AutoUpdateServiceProvider
 *
 * @package RawPHP\AutoUpdate
 */
class AutoUpdateServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes(
            [
                __DIR__ . '/../../config/auto-update.php' => config_path( 'auto-update.php' )
            ], 'config'
        );
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/auto-update.php', 'auto-update'
        );
    }
}