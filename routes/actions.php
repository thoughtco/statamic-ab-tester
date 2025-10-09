<?php

use Illuminate\Support\Facades\Route;
use Thoughtco\StatamicABTester\Http\Controllers\FrontendActionsController;

Route::name('ab-tester.')->group(function () {
    Route::post('/js', [FrontendActionsController::class, 'run'])->name('front-end-js');
});
