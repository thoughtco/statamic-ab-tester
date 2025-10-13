<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Statamic\Facades\User;
use Thoughtco\StatamicABTester\Facades\Experiment;

beforeEach(function () {
    $this->actingAs(User::make()->makeSuper()->save());
});

describe('Experiment Actions Controller', function () {
    it('can publish experiments', function () {
        $experiment = tap(Experiment::make('test')
            ->published(false))
            ->save();

        $this->post(cp_route('ab.experiments.actions'), [
            'action' => 'publish',
            'selections' => ['test'],
        ])->assertRedirect();

        expect($experiment->fresh()->published())->toBeTrue();
    });

    it('can unpublish experiments', function () {
        $experiment = tap(Experiment::make('test')
            ->published(true))
            ->save();

        $this->post(cp_route('ab.experiments.actions'), [
            'action' => 'unpublish',
            'selections' => ['test'],
        ])->assertRedirect();

        expect($experiment->fresh()->published())->toBeFalse();
    });

    it('can delete experiments', function () {
        $experiment = tap(Experiment::make('test'))->save();

        $this->post(cp_route('ab.experiments.actions'), [
            'action' => 'delete',
            'selections' => ['test'],
        ])->assertRedirect();

        expect(Experiment::find('test'))->toBeNull();
    });

    it('handles bulk actions on multiple experiments', function () {
        $exp1 = tap(Experiment::make('test-1')->published(false))->save();
        $exp2 = tap(Experiment::make('test-2')->published(false))->save();

        $this->post(cp_route('ab.experiments.actions'), [
            'action' => 'publish',
            'selections' => ['test-1', 'test-2'],
        ])->assertRedirect();

        expect($exp1->fresh()->published())->toBeTrue();
        expect($exp2->fresh()->published())->toBeTrue();
    });

    it('validates action requirements', function () {
        $this->post(cp_route('ab.experiments.actions'), [])
            ->assertSessionHasErrors(['action']);
    });
});
