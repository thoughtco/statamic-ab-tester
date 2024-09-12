<?php

use Illuminate\Support\Facades\Route;
use Thoughtco\ABTester\Http\Controllers;

Route::get(config('statamic.routes.action').'/ab/success/{params}', Controllers\GoalsController::class)
    ->name('ab.external_redirect');
