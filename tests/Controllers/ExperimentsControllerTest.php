
<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Statamic\Facades\User;
use Thoughtco\StatamicABTester\Facades\Experiment;

beforeEach(function () {
    $this->actingAs(User::make()->makeSuper()->save());
});

describe('Experiments Controller', function () {
    it('displays experiments index', function () {
        $this->get(cp_route('ab.experiments.index'))
            ->assertOk();
    });

    it('returns experiments json', function () {
        $experiment = tap(Experiment::make('test-experiment')
            ->title('Test Experiment')
            ->published(true))
            ->save();

        $this->get(cp_route('ab.experiments.json'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'published',
                    ],
                ],
            ]);
    });

    it('shows experiment create form', function () {
        $this->get(cp_route('ab.experiments.create'))
            ->assertOk();
    });

    it('creates new experiment', function () {
        $this->assertCount(0, Experiment::all());

        $data = [
            'title' => 'Test Experiment',
            'type' => 'manual',
            'published' => true,
            'goals' => [1],
            'manual_fields' => [
                'control' => ['weight' => 50],
                'variant_a' => ['weight' => 50],
            ],
        ];

        $this->post(cp_route('ab.experiments.store'), $data)
            ->assertStatus(200);

        $this->assertCount(1, Experiment::all());
    });

    it('validates experiment creation', function () {
        $this->post(cp_route('ab.experiments.store'), [])
            ->assertSessionHasErrors(['title']);
    });

    it('shows experiment', function () {
        $experiment = tap(Experiment::make('test-experiment')
            ->title('Test Experiment'))
            ->save();

        $this->get(cp_route('ab.experiments.show', $experiment->id()))
            ->assertOk();
    });

    it('updates experiment', function () {
        $experiment = tap(Experiment::make('test-experiment')
            ->title('Original Title'))
            ->save();

        $data = [
            'title' => 'Updated Experiment',
            'type' => 'manual',
            'goals' => [1],
            'published' => false,
        ];

        $this->patch(cp_route('ab.experiments.update', $experiment->id()), $data)
            ->assertStatus(200);

        expect($experiment->fresh()->title())->toBe('Updated Experiment');
    });

    it('deletes experiment', function () {
        $experiment = tap(Experiment::make('test-experiment')
            ->title('Test Experiment'))
            ->save();

        $this->delete(cp_route('ab.experiments.delete', $experiment->id()))
            ->assertStatus(200);

        expect(Experiment::find($experiment->id()))->toBeNull();
    });
});
