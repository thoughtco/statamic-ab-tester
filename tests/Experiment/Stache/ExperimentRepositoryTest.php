<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Statamic\Facades\File;
use Thoughtco\StatamicABTester\Experiment\Stache\Experiment;
use Thoughtco\StatamicABTester\Experiment\Stache\ExperimentQueryBuilder;
use Thoughtco\StatamicABTester\Facades\Experiment as ExperimentApi;

it('can make an experiment', function () {
    $this->assertInstanceOf(Experiment::class, ExperimentApi::make());
});

it('can save an experiment', function () {
    $experiment = tap(ExperimentApi::make()
        ->handle('test')
        ->title('Test')
        ->variants([]))
        ->save();

    $this->assertTrue(File::exists($experiment->path()));
});

it('can delete an experiment', function () {
    $experiment = tap(ExperimentApi::make()
        ->handle('test')
        ->title('Test')
        ->variants([]))
        ->save();

    $this->assertTrue(File::exists($experiment->path()));

    $experiment->delete();

    $this->assertFalse(File::exists($experiment->path()));
});

it('gets a query builder', function () {
    $this->assertInstanceOf(ExperimentQueryBuilder::class, ExperimentApi::query());
});

it('find an experiment', function () {
    $experiment = tap(ExperimentApi::make()
        ->handle('test')
        ->title('Test')
        ->variants([]))
        ->save();

    $this->assertNotNull(ExperimentApi::find('test'));
    $this->assertCount(1, ExperimentApi::all());
});
