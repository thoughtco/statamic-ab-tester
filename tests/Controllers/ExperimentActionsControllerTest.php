<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Statamic\Facades\User;
use Thoughtco\StatamicABTester\Facades\Experiment;

beforeEach(function () {
    $this->actingAs(tap(User::make()->makeSuper())->save());
});

describe('Experiment Actions Controller', function () {
    it('can delete experiments', function () {
        $experiment = tap(Experiment::make('test'))->save();

        $this->post(cp_route('ab.experiments.actions'), [
            'action' => 'delete_experiment',
            'selections' => [$experiment->id()],
            'values' => [],
        ])
            ->assertOk()
            ->assertSessionHasNoErrors();

        expect(Experiment::find('test'))->toBeNull();
    });

    it('handles bulk actions on multiple experiments', function () {
        $exp1 = tap(Experiment::make('test-1')->published(false))->save();
        $exp2 = tap(Experiment::make('test-2')->published(false))->save();

        expect(Experiment::all()->count())->toBe(2);

        $this->post(cp_route('ab.experiments.actions'), [
            'action' => 'delete_experiment',
            'selections' => [$exp1->id(), $exp2->id()],
            'values' => [],
        ])->assertOk();

        expect(Experiment::all()->count())->toBe(0);
    });

    it('validates action requirements', function () {
        $this->post(cp_route('ab.experiments.actions'), [])
            ->assertSessionHasErrors(['action']);
    });
});
