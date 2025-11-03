<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Statamic\Facades\User;
use Thoughtco\StatamicABTester\Facades\Goal;

beforeEach(function () {
    $this->actingAs(User::make()->makeSuper()->save());
});

describe('Goals Controller', function () {
    it('displays goals index', function () {
        $this->get(cp_route('ab.goals.index'))
            ->assertOk();
    });

    it('returns goals json', function () {
        $goal = tap(Goal::make('test-goal')
            ->title('Test Goal'))
            ->save();

        $this->get(cp_route('ab.goals.json'))
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'handle',
                    ],
                ],
            ]);
    });

    it('shows goal create form', function () {
        $this->get(cp_route('ab.goals.create'))
            ->assertOk();
    });

    it('creates new goal', function () {
        $data = [
            'title' => 'Test Goal',
            'handle' => 'test-goal',
            'description' => 'A test goal',
        ];

        $response = $this->post(cp_route('ab.goals.store'), $data)
            ->assertStatus(200);

        $goal = Goal::query()->where('handle', 'test-goal')->first();
        expect($goal)->not->toBeNull();

        $response->assertSimilarJson(['redirect' => url('/cp/ab/goals/'.$goal->id())]);
    });

    it('validates goal creation', function () {
        $this->post(cp_route('ab.goals.store'), [])
            ->assertSessionHasErrors(['title', 'handle']);
    });

    it('shows goal', function () {
        $goal = tap(Goal::make('test-goal')
            ->title('Test Goal'))
            ->save();

        $this->get(cp_route('ab.goals.show', $goal->id()))
            ->assertOk();
    });

    it('updates goal', function () {
        $goal = tap(Goal::make('test-goal')
            ->title('Original Title'))
            ->save();

        $data = [
            'title' => 'Updated Goal',
            'handle' => 'test-goal',
        ];

        $response = $this->patch(cp_route('ab.goals.update', $goal->id()), $data)
            ->assertStatus(200);

        expect($goal->fresh()->title())->toBe('Updated Goal');
    });

    it('deletes goal', function () {
        $goal = tap(Goal::make('test-goal')
            ->title('Test Goal')
            ->handle('test'))
            ->save();

        $this->delete(cp_route('ab.goals.delete', $goal->id()))
            ->assertStatus(200);

        expect(Goal::find($goal->id()))->toBeNull();
    });
});
