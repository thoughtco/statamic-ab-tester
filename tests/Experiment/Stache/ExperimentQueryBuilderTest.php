<?php

uses(TestCase::class);

use Thoughtco\StatamicABTester\Facades\Experiment as ExperimentApi;
use Thoughtco\StatamicABTester\Tests\TestCase;

it('queries experiments', function () {
    $experiment = tap(ExperimentApi::make()
        ->id('test')
        ->title('Test'))
        ->save();

    $this->assertSame(1, ExperimentApi::query()->where('id', 'test')->count());
    $this->assertSame(0, ExperimentApi::query()->where('id', 'not-test')->count());
});
