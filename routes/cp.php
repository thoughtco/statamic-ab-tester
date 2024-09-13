<?php

use Illuminate\Support\Facades\Route;
use Thoughtco\StatamicABTester\Http\Controllers\ExperimentResultsController;
use Thoughtco\StatamicABTester\Http\Controllers\ExperimentsController;

Route::get('ab/experiments', [ExperimentsController::class, 'index'])->name('ab.experiments.index');
Route::get('ab/experiments/create', [ExperimentsController::class, 'create'])->name('ab.experiments.create');
Route::get('ab/experiments/{experiment}', [ExperimentsController::class, 'show'])->name('ab.experiments.show');
Route::post('ab/experiments', [ExperimentsController::class, 'store'])->name('ab.experiments.store');
Route::get('ab/experiments/{experiment}/edit', [ExperimentsController::class, 'edit'])->name('ab.experiments.edit');
Route::delete('ab/experiments/{experiment}/delete', [ExperimentsController::class, 'destroy'])->name('ab.experiments.delete');
Route::patch('ab/experiments/{experiment}', [ExperimentsController::class, 'update'])->name('ab.experiments.update');

Route::get('ab/experiments/{experiment}/results', [ExperimentResultsController::class, 'show'])->name('ab.experiments.results.show');
