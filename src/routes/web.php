<?php
use Illuminate\Support\Facades\Route;
use ME\Portfolio\Http\Controllers\PortfolioController;
use ME\Portfolio\Http\Controllers\ProjectController;
use ME\Portfolio\Http\Controllers\AppearanceController;
use ME\Http\Middleware\LocaleMiddleware;

Route::get('/', [PortfolioController::class, 'index'])->middleware('activityLog')
     ->name('index');

// CSS/JS/images bundled with the package, served directly so no
// vendor:publish step is needed on deploy.
Route::get('/portfolio-assets/{path}', [PortfolioController::class, 'asset'])
     ->where('path', '[A-Za-z0-9_\-/]+\.(css|js|svg|png|jpe?g|webp|ico)')
     ->name('portfolio.asset');

Route::get('/sitemap.xml', [PortfolioController::class, 'sitemap'])
    ->name('sitemap.xml');

Route::get('/robots.txt', [PortfolioController::class, 'robots'])
    ->name('robots.txt');



Route::middleware(['web', 'auth', LocaleMiddleware::class, 'activityLog'])->group(function () {
    Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::resource('projects', ProjectController::class);

        // Colour preset, cursor trail and profile photo for the public page.
        Route::middleware('authorization:portfolio.appearance')->group(function () {
            Route::get('appearance', [AppearanceController::class, 'edit'])->name('appearance.edit');
            Route::put('appearance', [AppearanceController::class, 'update'])->name('appearance.update');
        });
    });
});
