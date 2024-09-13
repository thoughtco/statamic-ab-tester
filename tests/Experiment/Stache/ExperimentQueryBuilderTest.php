<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Thoughtco\StatamicABTester\Facades\Experiment as ExperimentApi;

it('queries experiments', function () {
    $experiment = tap(ExperimentApi::make()
        ->handle('test')
        ->title('Test')
        ->variants([]))
        ->save();

    $this->assertSame(1, ExperimentApi::query()->where('handle', 'test')->count());
    $this->assertSame(0, ExperimentApi::query()->where('handle', 'not-test')->count());
});
