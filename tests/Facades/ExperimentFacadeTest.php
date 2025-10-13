
<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Statamic\Data\DataCollection;
use Thoughtco\StatamicABTester\Facades\Experiment;

describe('Experiment Facade', function () {
    it('can get all experiments', function () {
        $experiments = Experiment::all();

        expect($experiments)->toBeInstanceOf(DataCollection::class);
    });

    it('can create experiment', function () {
        $experiment = Experiment::make('test-experiment')
            ->title('Test Experiment')
            ->type('manual')
            ->published(true);

        expect($experiment->handle())->toBe('test-experiment');
        expect($experiment->title())->toBe('Test Experiment');
        expect($experiment->published())->toBeTrue();
    });

    it('can find experiment', function () {
        $experiment = Experiment::make('findable')
            ->title('Findable Experiment')
            ->save();

        $found = Experiment::find('findable');

        expect($found)->not->toBeNull();
        expect($found->title())->toBe('Findable Experiment');
    });

    it('returns null for non-existent experiment', function () {
        $experiment = Experiment::find('non-existent');

        expect($experiment)->toBeNull();
    });

    it('can query experiments', function () {
        Experiment::make('exp-1')->title('First')->save();
        Experiment::make('exp-2')->title('Second')->save();

        $experiments = Experiment::query()->where('title', 'First')->get();

        expect($experiments)->toHaveCount(1);
        expect($experiments->first()->title())->toBe('First');
    });

    it('can record hit', function () {
        $experiment = Experiment::make('test')->save();

        $experiment->recordHit('control', ['custom' => 'data']);

        $this->assertDatabaseHas('ab_test_results', [
            'experiment_id' => 'test',
            'variant' => 'control',
            'type' => 'hit',
        ]);
    });

    it('can record success', function () {
        $experiment = Experiment::make('test')->save();

        $experiment->recordSuccess('variant_a', 'goal-id');

        $this->assertDatabaseHas('ab_test_results', [
            'experiment_id' => 'test',
            'variant' => 'variant_a',
            'type' => 'success',
            'goal_id' => 'goal-id',
        ]);
    });

    it('can record failure', function () {
        $experiment = Experiment::make('test')->save();

        $experiment->recordFailure('variant_a', 'goal-id');

        $this->assertDatabaseHas('ab_test_results', [
            'experiment_id' => 'test',
            'variant' => 'variant_a',
            'type' => 'failure',
            'goal_id' => 'goal-id',
        ]);
    });

    it('can get results', function () {
        $experiment = Experiment::make('test')->save();
        $experiment->recordHit('control');
        $experiment->recordSuccess('control', 'goal');

        $results = $experiment->resultsQuery()->get();

        expect($results)->toHaveCount(2);
    });
});
