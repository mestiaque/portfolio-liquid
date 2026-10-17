<?php
use Illuminate\Support\Facades\Route;
use ME\Portfolio\Http\Controllers\PortfolioController;

Route::prefix('api')
    ->middleware('throttle:5,1')
    ->post('/messages-store', [PortfolioController::class, 'storeMessage'])
    ->name('portfolio.messages.store');
