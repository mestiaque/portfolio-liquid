<?php
use Illuminate\Support\Facades\Route;
use ME\Portfolio\Http\Controllers\PortfolioController;
use ME\Portfolio\Http\Controllers\ProjectController;
use ME\Portfolio\Http\Controllers\AppearanceController;
use ME\Portfolio\Http\Controllers\PortfolioSettingsController;
use ME\Portfolio\Http\Controllers\CvController;
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

// Public CV (Bangladeshi format) and ATS-friendly resume, both from the cv table and shown in
// PrintMaster 2; ?lang=bn shows the CV's section titles in Bangla.
Route::middleware(['web', 'activityLog'])->group(function () {
    Route::get('/cv', [CvController::class, 'show'])->name('cv.show');
    Route::get('/resume', [CvController::class, 'resume'])->name('resume.show');
    Route::post('/cv/unlock', [CvController::class, 'unlock'])->middleware('throttle:10,1')->name('cv.unlock');
});



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
