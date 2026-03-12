<?php

uses(Thoughtco\StatamicABTester\Tests\TestCase::class);

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

    it('duplicates a manual experiment', function () {
        $experiment = tap(Experiment::make('original')
            ->title('My Experiment')
            ->type('manual')
            ->goals(['goal-1'])
            ->data([
                'manual_fields' => [
                    ['handle' => 'control', 'label' => 'Control', 'weight' => 50],
                    ['handle' => 'variant_a', 'label' => 'Variant A', 'weight' => 50],
                ],
            ])
            ->published(true))
            ->save();

        $this->post(cp_route('ab.experiments.actions'), [
            'action' => 'duplicate_experiment',
            'selections' => [$experiment->id()],
            'values' => [],
        ])->assertOk();

        expect(Experiment::all()->count())->toBe(2);

        $copy = Experiment::all()->reject(fn ($e) => $e->id() === $experiment->id())->first();
        expect($copy->title())->toBe('Copy of My Experiment');
        expect($copy->type())->toBe('manual');
        expect($copy->goals())->toBe(['goal-1']);
        expect($copy->published())->toBeFalse();
        expect($copy->completedAt())->toBeNull();
        expect($copy->get('manual_fields'))->toBe($experiment->get('manual_fields'));
    });

    it('duplicates an item experiment preserving experiment_fields and traffic_split', function () {
        $experiment = tap(Experiment::make('item-exp')
            ->title('Item Experiment')
            ->type('item')
            ->goals(['goal-1'])
            ->data([
                'item_id' => 'abc-123',
                'experiment_fields' => ['fields' => ['title'], 'values' => ['title' => 'Variant Title']],
                'traffic_split' => 30,
            ])
            ->published(true))
            ->save();

        $this->post(cp_route('ab.experiments.actions'), [
            'action' => 'duplicate_experiment',
            'selections' => [$experiment->id()],
            'values' => [],
        ])->assertOk();

        $copy = Experiment::all()->reject(fn ($e) => $e->id() === $experiment->id())->first();
        expect($copy->title())->toBe('Copy of Item Experiment');
        expect($copy->type())->toBe('item');
        expect($copy->get('item_id'))->toBe('abc-123');
        expect($copy->get('traffic_split'))->toBe(30);
        expect($copy->get('experiment_fields'))->toBe($experiment->get('experiment_fields'));
        expect($copy->published())->toBeFalse();
    });

    it('redirects to edit page when duplicating a single experiment', function () {
        $experiment = tap(Experiment::make('dup-redirect')
            ->title('Redirect Test')
            ->type('manual')
            ->goals([]))
            ->save();

        $response = $this->post(cp_route('ab.experiments.actions'), [
            'action' => 'duplicate_experiment',
            'selections' => [$experiment->id()],
            'values' => [],
        ])->assertOk();

        $copyId = Experiment::all()
            ->reject(fn ($e) => $e->id() === $experiment->id())
            ->first()
            ->id();

        expect($response->json('redirect'))->toBe(cp_route('ab.experiments.edit', $copyId));
    });

    it('duplicates multiple experiments in bulk without redirecting', function () {
        $exp1 = tap(Experiment::make('bulk-1')->title('Experiment One')->type('manual')->goals([]))->save();
        $exp2 = tap(Experiment::make('bulk-2')->title('Experiment Two')->type('manual')->goals([]))->save();

        $response = $this->post(cp_route('ab.experiments.actions'), [
            'action' => 'duplicate_experiment',
            'selections' => [$exp1->id(), $exp2->id()],
            'values' => [],
        ])->assertOk();

        expect(Experiment::all()->count())->toBe(4);
        expect($response->json('redirect'))->toBeFalsy();
    });

    it('does not copy completed_at when duplicating a completed experiment', function () {
        $experiment = tap(Experiment::make('completed-exp')
            ->title('Completed Experiment')
            ->type('manual')
            ->goals([])
            ->completedAt(now()))
            ->save();

        $this->post(cp_route('ab.experiments.actions'), [
            'action' => 'duplicate_experiment',
            'selections' => [$experiment->id()],
            'values' => [],
        ])->assertOk();

        $copy = Experiment::all()->reject(fn ($e) => $e->id() === $experiment->id())->first();
        expect($copy->completedAt())->toBeNull();
        expect($copy->published())->toBeFalse();
    });
});
