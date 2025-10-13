<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Thoughtco\StatamicABTester\Models\ABTestResult;

describe('AB Test Result Model', function () {
    it('can create hit result', function () {
        $result = ABTestResult::create([
            'experiment_id' => 'test-experiment',
            'variant' => 'control',
            'type' => 'hit',
            'ip_address' => '127.0.0.1',
            'user_id' => null,
            'custom_data' => json_encode(['page' => '/home']),
        ]);

        expect($result)->toBeInstanceOf(ABTestResult::class);
        expect($result->experiment_id)->toBe('test-experiment');
        expect($result->variant)->toBe('control');
        expect($result->type)->toBe('hit');

        $this->assertDatabaseHas('ab_test_results', [
            'experiment_id' => 'test-experiment',
            'variant' => 'control',
            'type' => 'hit',
        ]);
    });

    it('can create success result', function () {
        $result = ABTestResult::create([
            'experiment_id' => 'test-experiment',
            'variant' => 'variant_a',
            'type' => 'success',
            'goal_id' => 'signup',
            'ip_address' => '127.0.0.1',
        ]);

        expect($result->type)->toBe('success');
        expect($result->goal_id)->toBe('signup');
    });

    it('can create failure result', function () {
        $result = ABTestResult::create([
            'experiment_id' => 'test-experiment',
            'variant' => 'variant_a',
            'type' => 'failure',
            'goal_id' => 'signup',
            'ip_address' => '127.0.0.1',
        ]);

        expect($result->type)->toBe('failure');
        expect($result->goal_id)->toBe('signup');
    });

    it('scopes by experiment', function () {
        ABTestResult::create([
            'experiment_id' => 'exp-1',
            'variant' => 'control',
            'type' => 'hit',
        ]);

        ABTestResult::create([
            'experiment_id' => 'exp-2',
            'variant' => 'control',
            'type' => 'hit',
        ]);

        $results = ABTestResult::forExperiment('exp-1')->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->experiment_id)->toBe('exp-1');
    });

    it('scopes by variant', function () {
        ABTestResult::create([
            'experiment_id' => 'test',
            'variant' => 'control',
            'type' => 'hit',
        ]);

        ABTestResult::create([
            'experiment_id' => 'test',
            'variant' => 'variant_a',
            'type' => 'hit',
        ]);

        $results = ABTestResult::forVariant('control')->get();

        expect($results)->toHaveCount(1);
        expect($results->first()->variant)->toBe('control');
    });

    it('scopes by type', function () {
        ABTestResult::create([
            'experiment_id' => 'test',
            'variant' => 'control',
            'type' => 'hit',
        ]);

        ABTestResult::create([
            'experiment_id' => 'test',
            'variant' => 'control',
            'type' => 'success',
        ]);

        $hits = ABTestResult::ofType('hit')->get();
        $successes = ABTestResult::ofType('success')->get();

        expect($hits)->toHaveCount(1);
        expect($successes)->toHaveCount(1);
        expect($hits->first()->type)->toBe('hit');
        expect($successes->first()->type)->toBe('success');
    });
});
