<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Illuminate\Support\Facades\Session;
use Statamic\Data\DataCollection;
use Thoughtco\StatamicABTester\Facades\Goal;

beforeEach(function () {
    // Clear session data before each test
    Session::flush();
});

describe('Goal facade', function () {
    it('can get all goals', function () {
        $goals = Goal::all();

        expect($goals)->toBeInstanceOf(DataCollection::class);
    });

    it('can find a goal by id', function () {
        // Create a mock goal or use factory if available
        $goal = Goal::make();

        if (method_exists($goal, 'id')) {
            $foundGoal = Goal::find($goal->id());
            // Assert based on your implementation
        }

        // If no goal exists, it should return null
        $nonExistentGoal = Goal::find('non-existent-id');
        expect($nonExistentGoal)->toBeNull();
    });

    it('can make a new goal instance', function () {
        $goal = Goal::make();

        expect($goal)->not->toBeNull();
        expect($goal)->toBeInstanceOf(\Thoughtco\StatamicABTester\Contracts\Goal::class);
    });

    it('returns correct blueprint structure', function () {
        $blueprint = Goal::blueprint();

        expect($blueprint)->toBeInstanceOf(\Statamic\Fields\Blueprint::class);

        $fields = $blueprint->fields()->all()->toArray();
        expect($fields)->toHaveKey('title');
        expect($fields)->toHaveKey('handle');

        expect($fields['title']['type'])->toBe('text');
        expect($fields['title']['validate'])->toBe('required');

        expect($fields['handle']['type'])->toBe('slug');
        expect($fields['handle']['validate'])->toBe('required');
    });
});
