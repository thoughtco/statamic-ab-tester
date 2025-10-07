<?php

use Illuminate\Support\Facades\Route;
use Thoughtco\StatamicABTester\Http\Controllers\ExperimentResultsController;
use Thoughtco\StatamicABTester\Http\Controllers\ExperimentsController;

Route::name('ab.experiments.')->prefix('ab/experiments')->group(function () {
    Route::get('', [ExperimentsController::class, 'index'])->name('index');
    Route::get('/json', [ExperimentsController::class, 'json'])->name('json');
    Route::get('/actions', [ExperimentsController::class, 'json'])->name('actions');

    Route::get('/{experiment}', [ExperimentsController::class, 'show'])->name('show');
    Route::post('/', [ExperimentsController::class, 'store'])->name('store');
    Route::get('/{experiment}/edit', [ExperimentsController::class, 'edit'])->name('edit');
    Route::delete('/{experiment}/delete', [ExperimentsController::class, 'destroy'])->name('delete');
    Route::patch('/{experiment}', [ExperimentsController::class, 'update'])->name('update');

    Route::get('/{experiment}/results', [ExperimentResultsController::class, 'show'])->name('results.show');
});

Route::name('ab.goals.')->prefix('ab/goals')->group(function () {
    Route::get('', [ExperimentsController::class, 'index'])->name('index');
});
