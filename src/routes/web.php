<?php
use Illuminate\Support\Facades\Route;
use ME\Portfolio\Http\Controllers\PortfolioController;
use ME\Portfolio\Http\Controllers\ProjectController;
use ME\Portfolio\Http\Controllers\AppearanceController;
use ME\Portfolio\Http\Controllers\PortfolioSettingsController;
use ME\Http\Middleware\LocaleMiddleware;

Route::get('/', [PortfolioController::class, 'index'])->middleware('activityLog')
     ->name('index');

Route::get('/portfolio-assets/{path}', [PortfolioController::class, 'asset'])
     ->where('path', '[A-Za-z0-9_\-/]+\.(css|js|svg|png|jpe?g|webp|ico)')
     ->name('portfolio.asset');

Route::get('/sitemap.xml', [PortfolioController::class, 'sitemap'])
    ->name('sitemap.xml');

Route::get('/robots.txt', [PortfolioController::class, 'robots'])
    ->name('robots.txt');



Route::middleware(['web', 'auth', LocaleMiddleware::class, 'activityLog'])
    ->prefix(me_prefix())
    ->name(me_prefix().'.')
    ->group(function () {
    Route::resource('projects', ProjectController::class);
    Route::get('appearance', [AppearanceController::class, 'edit'])->name('appearance.edit');
    Route::put('appearance', [AppearanceController::class, 'update'])->name('appearance.update');
    Route::get('portfolio-settings', [PortfolioSettingsController::class, 'edit'])->name('portfolio-settings.edit');
    Route::put('portfolio-settings', [PortfolioSettingsController::class, 'update'])->name('portfolio-settings.update');
    Route::get('portfolio-settings/export', [PortfolioSettingsController::class, 'export'])->name('portfolio-settings.export');
    Route::post('portfolio-settings/import', [PortfolioSettingsController::class, 'import'])->name('portfolio-settings.import');
});
