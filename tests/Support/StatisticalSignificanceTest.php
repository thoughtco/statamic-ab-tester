<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Thoughtco\StatamicABTester\Support\StatisticalSignificance;

describe('StatisticalSignificance', function () {
    it('returns null when variant A has no hits', function () {
        expect(StatisticalSignificance::calculate(0, 0, 100, 10))->toBeNull();
    });

    it('returns null when variant B has no hits', function () {
        expect(StatisticalSignificance::calculate(100, 10, 0, 0))->toBeNull();
    });

    it('returns null when both variants have zero conversions', function () {
        expect(StatisticalSignificance::calculate(100, 0, 100, 0))->toBeNull();
    });

    it('returns null when both variants have 100% conversion rate', function () {
        expect(StatisticalSignificance::calculate(100, 100, 100, 100))->toBeNull();
    });

    it('detects a statistically significant difference with large samples', function () {
        // 10% vs 15% conversion with 1000 each is significant
        $result = StatisticalSignificance::calculate(1000, 100, 1000, 150);

        expect($result)->not->toBeNull();
        expect($result['is_significant'])->toBeTrue();
        expect($result['confidence'])->toBeGreaterThan(95.0);
        expect($result['p_value'])->toBeLessThan(0.05);
    });

    it('does not flag a difference as significant with small samples', function () {
        // 10% vs 12% with only 50 each is not significant
        $result = StatisticalSignificance::calculate(50, 5, 50, 6);

        expect($result)->not->toBeNull();
        expect($result['is_significant'])->toBeFalse();
    });

    it('returns all required fields', function () {
        $result = StatisticalSignificance::calculate(500, 50, 500, 75);

        expect($result)->toHaveKeys(['z_score', 'p_value', 'confidence', 'is_significant']);
        expect($result['z_score'])->toBeFloat();
        expect($result['p_value'])->toBeFloat();
        expect($result['confidence'])->toBeFloat();
        expect($result['is_significant'])->toBeBool();
    });

    it('keeps confidence between 0 and 100', function () {
        $result = StatisticalSignificance::calculate(100, 10, 100, 20);

        expect($result['confidence'])->toBeGreaterThanOrEqual(0.0);
        expect($result['confidence'])->toBeLessThanOrEqual(100.0);
    });

    it('keeps p_value between 0 and 1', function () {
        $result = StatisticalSignificance::calculate(100, 10, 100, 20);

        expect($result['p_value'])->toBeGreaterThanOrEqual(0.0);
        expect($result['p_value'])->toBeLessThanOrEqual(1.0);
    });

    it('is symmetric — swapping variants produces the same confidence and p_value', function () {
        $result1 = StatisticalSignificance::calculate(1000, 100, 1000, 150);
        $result2 = StatisticalSignificance::calculate(1000, 150, 1000, 100);

        expect($result1['confidence'])->toBe($result2['confidence']);
        expect($result1['p_value'])->toBe($result2['p_value']);
        expect(abs($result1['z_score']))->toBe(abs($result2['z_score']));
    });

    it('produces opposite z_score signs when variants are swapped', function () {
        $result1 = StatisticalSignificance::calculate(1000, 100, 1000, 150);
        $result2 = StatisticalSignificance::calculate(1000, 150, 1000, 100);

        expect($result1['z_score'])->toBe(-$result2['z_score']);
    });

    it('gives a positive z_score when variant B outperforms variant A', function () {
        $result = StatisticalSignificance::calculate(1000, 100, 1000, 150);

        expect($result['z_score'])->toBeGreaterThan(0);
    });

    it('gives a negative z_score when variant A outperforms variant B', function () {
        $result = StatisticalSignificance::calculate(1000, 150, 1000, 100);

        expect($result['z_score'])->toBeLessThan(0);
    });

    it('marks identical conversion rates as not significant', function () {
        $result = StatisticalSignificance::calculate(500, 50, 500, 50);

        expect($result['is_significant'])->toBeFalse();
        expect($result['confidence'])->toBe(0.0);
    });
});
