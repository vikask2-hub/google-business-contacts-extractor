<?php

use App\Http\Controllers\GmbExtractorController;
use Illuminate\Support\Facades\Route;

Route::redirect('/portfolio', 'https://tech4projects.online/')->name('portfolio');
Route::redirect('/', '/gmb-extractor');

Route::controller(GmbExtractorController::class)->prefix('gmb-extractor')->name('gmb-extractor.')->middleware('throttle:30,1')->group(function (): void {
    Route::get('/', 'index')->name('index');
    Route::get('/search', 'search')->name('search');
});
