<?php

use Illuminate\Support\Facades\Session;
use Statamic\Facades\User;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Facades\Goal;
use Thoughtco\StatamicABTester\Models\AbTestResult;
use Thoughtco\StatamicABTester\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Session::flush();

    $this->goal = tap(Goal::make()->handle('basket')->title('Add to basket'))->save();

    $this->experiment = tap(Experiment::make()
        ->title('Button Test')
        ->type('manual')
        ->published(true)
        ->goals([$this->goal->id()])
        ->data([
            'manual_fields' => [
                ['handle' => 'control', 'weight' => 50],
                ['handle' => 'red_button', 'weight' => 50],
            ],
        ]))
        ->save();
});

describe('Goal tracking', function () {
    it('records a completed goal against the variant in the session', function () {
        session()->put('statamic.ab.'.$this->experiment->id(), 'red_button');

        Goal::completed('basket');

        $this->assertDatabaseHas('ab_test_results', [
            'experiment_id' => $this->experiment->id(),
            'goal_id' => $this->goal->id(),
            'type' => 'success',
            'variation' => 'red_button',
        ]);
    });

    it('records a failed goal against the variant in the session', function () {
        session()->put('statamic.ab.'.$this->experiment->id(), 'control');

        Goal::failed('basket');

        $this->assertDatabaseHas('ab_test_results', [
            'experiment_id' => $this->experiment->id(),
            'goal_id' => $this->goal->id(),
            'type' => 'failure',
            'variation' => 'control',
        ]);
    });

    it('does not record a goal for a visitor who has not seen the experiment', function () {
        Goal::completed('basket');

        expect(AbTestResult::count())->toBe(0);
    });

    it('records a goal once per visitor', function () {
        session()->put('statamic.ab.'.$this->experiment->id(), 'control');

        Goal::completed('basket');
        Goal::completed('basket');

        expect(AbTestResult::where('type', 'success')->count())->toBe(1);
    });

    it('records a goal for each visitor on the same variant', function () {
        session()->put('statamic.ab.'.$this->experiment->id(), 'control');
        Goal::completed('basket');

        Session::flush();

        session()->put('statamic.ab.'.$this->experiment->id(), 'control');
        Goal::completed('basket');

        expect(AbTestResult::where('type', 'success')->where('variation', 'control')->count())->toBe(2);
    });
});

describe('Frontend actions', function () {
    it('records a hit against the variant in the session', function () {
        $this->withSession(['statamic.ab.'.$this->experiment->id() => 'red_button'])
            ->postJson(route('statamic.ab-tester.front-end-js'), ['type' => 'hit', 'source' => $this->experiment->id(), 'data' => ['page' => 'home']])
            ->assertOk();

        $this->assertDatabaseHas('ab_test_results', [
            'experiment_id' => $this->experiment->id(),
            'type' => 'hit',
            'variation' => 'red_button',
        ]);
    });

    it('records a success', function () {
        $this->withSession(['statamic.ab.'.$this->experiment->id() => 'red_button'])
            ->postJson(route('statamic.ab-tester.front-end-js'), ['type' => 'success', 'source' => 'basket'])
            ->assertOk();

        $this->assertDatabaseHas('ab_test_results', [
            'experiment_id' => $this->experiment->id(),
            'type' => 'success',
            'variation' => 'red_button',
        ]);
    });

    it('records a failure', function () {
        $this->withSession(['statamic.ab.'.$this->experiment->id() => 'red_button'])
            ->postJson(route('statamic.ab-tester.front-end-js'), ['type' => 'failure', 'source' => 'basket'])
            ->assertOk();

        $this->assertDatabaseHas('ab_test_results', [
            'experiment_id' => $this->experiment->id(),
            'type' => 'failure',
            'variation' => 'red_button',
        ]);
    });
});

describe('Experiment results', function () {
    it('only counts hits as hits', function () {
        $this->actingAs(User::make()->makeSuper()->save());

        foreach (['hit', 'hit', 'hit', 'hit', 'success', 'failure'] as $type) {
            AbTestResult::create([
                'experiment_id' => $this->experiment->id(),
                'variation' => 'control',
                'type' => $type,
                'data' => [],
            ]);
        }

        $this->get(cp_route('ab.experiments.show', $this->experiment->id()))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('results.variant.0.hits', 4)
                ->where('results.variant.0.success', 1)
                ->where('results.variant.0.failed', 1)
                ->where('results.variant.0.rate', 25)
            );
    });
});
