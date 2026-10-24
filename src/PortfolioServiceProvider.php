<?php
namespace ME\Portfolio;

use Illuminate\Support\ServiceProvider;

class PortfolioServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        // The package's own views always win, so a deploy can't be held back by an old copy in
        // resources/views/vendor/portfolio. Set PORTFOLIO_PUBLISHED_VIEWS=true to use published views.
        if (config('portfolio.published_views', false)) {
            $this->loadViewsFrom(__DIR__ . '/resources/views', 'portfolio');
        } else {
            $this->callAfterResolving('view', fn ($view) => $view->addNamespace('portfolio', __DIR__ . '/resources/views'));
        }
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'portfolio');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \ME\Portfolio\Console\ExportPortfolio::class,
                \ME\Portfolio\Console\ImportPortfolio::class,
                \ME\Portfolio\Console\SyncPortfolio::class,
            ]);
        }

        // Text saved in Admin → Portfolio Settings overrides config/portfolio.php. Skipped in the
        // console so config:cache never bakes database values into the cached config.
        $this->app->booted(function () {
            if (!$this->app->runningInConsole()) {
                // A new portfolio-sync.json (git pull / composer update) is imported once, automatically.
                try {
                    \ME\Portfolio\Support\Sync::applyIfChanged();
                } catch (\Throwable $e) {
                    report($e);
                }
                \ME\Portfolio\Support\Content::apply();
            }
        });

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

        $this->mergeConfigFrom(__DIR__ . '/Config/themes.php', 'portfolio_themes');

        if (file_exists(__DIR__ . '/Config/sidebar.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/sidebar.php', 'sidebar');
        }

        if (file_exists(__DIR__ . '/Config/permissions.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/permissions.php', 'permissions');
        }
    }
}
