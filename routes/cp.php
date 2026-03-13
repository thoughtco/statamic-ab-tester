<?php

use Illuminate\Support\Facades\Route;
use Thoughtco\StatamicABTester\Http\Controllers\ExperimentActionsController;
use Thoughtco\StatamicABTester\Http\Controllers\ExperimentsController;
use Thoughtco\StatamicABTester\Http\Controllers\GoalActionsController;
use Thoughtco\StatamicABTester\Http\Controllers\GoalsController;

Route::name('ab.experiments.')->prefix('ab/experiments')->group(function () {
    Route::get('', [ExperimentsController::class, 'index'])->name('index');
    Route::get('/json', [ExperimentsController::class, 'json'])->name('json');

    Route::post('/actions', [ExperimentActionsController::class, 'run'])->name('actions');
    Route::post('/actions/list', [ExperimentActionsController::class, 'bulkActions'])->name('actions.bulk');

    Route::get('/create', [ExperimentsController::class, 'create'])->name('create');
    Route::post('/', [ExperimentsController::class, 'store'])->name('store');

    Route::get('/{experiment}', [ExperimentsController::class, 'show'])->name('show');
    Route::get('/{experiment}/export', [ExperimentsController::class, 'export'])->name('export');
    Route::post('/{experiment}/complete', [ExperimentsController::class, 'complete'])->name('complete');
    Route::get('/{experiment}/edit', [ExperimentsController::class, 'edit'])->name('edit');
    Route::delete('/{experiment}/delete', [ExperimentsController::class, 'destroy'])->name('delete');
    Route::patch('/{experiment}', [ExperimentsController::class, 'update'])->name('update');
});

Route::name('ab.goals.')->prefix('ab/goals')->group(function () {
    Route::get('', [GoalsController::class, 'index'])->name('index');
    Route::get('/json', [GoalsController::class, 'json'])->name('json');

    Route::post('/actions', [GoalActionsController::class, 'run'])->name('actions');
    Route::post('/actions/list', [GoalActionsController::class, 'bulkActions'])->name('actions.bulk');

    Route::get('/create', [GoalsController::class, 'create'])->name('create');
    Route::post('/', [GoalsController::class, 'store'])->name('store');

    Route::get('/{goal}', [GoalsController::class, 'show'])->name('show');
    Route::get('/{goal}/edit', [GoalsController::class, 'edit'])->name('edit');
    Route::delete('/{goal}/delete', [GoalsController::class, 'destroy'])->name('delete');
    Route::patch('/{goal}', [GoalsController::class, 'update'])->name('update');
});
