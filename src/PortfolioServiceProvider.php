<?php
namespace ME\Portfolio;

use Illuminate\Support\ServiceProvider;

class PortfolioServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'portfolio');
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'portfolio');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->publishes([
            __DIR__ . '/Config/config.php' => config_path('portfolio.php'),
        ], 'portfolio-config');

        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/portfolio'),
        ], 'portfolio-views');

        // Optional: the assets are also served straight from the package by
        // the portfolio.asset route, so publishing is not required.
        $this->publishes([
            __DIR__ . '/public/portfolio' => public_path('vendor/portfolio'),
        ], 'portfolio-assets');
    }

    public function register()
    {
        if (file_exists(__DIR__ . '/Config/config.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/config.php', 'portfolio');
        }

        if (file_exists(__DIR__ . '/Config/sidebar.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/sidebar.php', 'sidebar');
        }

        if (file_exists(__DIR__ . '/Config/permissions.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/permissions.php', 'permissions');
        }
    }
}
