<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Models\AbTestResult;
use Thoughtco\StatamicABTester\Widgets\ABTesterWidget;

function makeWidget(): ABTesterWidget
{
    $widget = new ABTesterWidget;
    $widget->setConfig([]);

    return $widget;
}

describe('ABTesterWidget', function () {
    it('is registered with the correct handle', function () {
        expect(ABTesterWidget::handle())->toBe('ab_tester');
    });

    it('renders HTML', function () {
        $html = makeWidget()->html();

        expect($html)->toBeString()->not->toBeEmpty();
    });

    it('shows a count of zero when there are no active experiments', function () {
        $html = makeWidget()->html();

        expect($html)->toContain('0');
        expect($html)->toContain('No experiments are currently running.');
    });

    it('counts active experiments', function () {
        tap(Experiment::make('active-1')->title('Active One')->published(true))->save();
        tap(Experiment::make('active-2')->title('Active Two')->published(true))->save();

        $html = makeWidget()->html();

        expect($html)->toContain('2');
        expect($html)->toContain('Active One');
        expect($html)->toContain('Active Two');
    });

    it('excludes completed experiments', function () {
        tap(Experiment::make('active')->title('Active')->published(true))->save();

        tap(Experiment::make('done')->title('Done')->published(true))
            ->completedAt(now())
            ->save();

        $html = makeWidget()->html();

        expect($html)->toContain('Active');
        expect($html)->not->toContain('Done');
    });

    it('excludes unpublished experiments', function () {
        tap(Experiment::make('published')->title('Published')->published(true))->save();
        tap(Experiment::make('draft')->title('Draft')->published(false))->save();

        $html = makeWidget()->html();

        expect($html)->toContain('Published');
        expect($html)->not->toContain('Draft');
    });

    it('excludes experiments that have not started yet', function () {
        tap(Experiment::make('future')->title('Future')->published(true))
            ->startAt(now()->addDay())
            ->save();

        $html = makeWidget()->html();

        expect($html)->not->toContain('Future');
    });

    it('excludes experiments past their end date', function () {
        tap(Experiment::make('expired')->title('Expired')->published(true))
            ->endAt(now()->subDay())
            ->save();

        $html = makeWidget()->html();

        expect($html)->not->toContain('Expired');
    });

    it('shows total hits for each experiment', function () {
        $experiment = tap(Experiment::make('hit-test')->title('Hit Test')->published(true))->save();

        AbTestResult::create(['experiment_id' => $experiment->id(), 'variation' => '1', 'type' => 'hit', 'data' => []]);
        AbTestResult::create(['experiment_id' => $experiment->id(), 'variation' => '1', 'type' => 'hit', 'data' => []]);
        AbTestResult::create(['experiment_id' => $experiment->id(), 'variation' => '2', 'type' => 'hit', 'data' => []]);

        $html = makeWidget()->html();

        expect($html)->toContain('3');
    });

    it('shows the leading variant label', function () {
        $experiment = tap(Experiment::make('leader-test')->title('Leader Test')->published(true))->save();

        // variant 1: 1 hit, 1 success = 100% rate
        AbTestResult::create(['experiment_id' => $experiment->id(), 'variation' => 'control', 'type' => 'hit', 'data' => []]);
        AbTestResult::create(['experiment_id' => $experiment->id(), 'variation' => 'control', 'type' => 'success', 'data' => []]);
        // variant 2: 10 hits, 0 successes = 0% rate
        for ($i = 0; $i < 10; $i++) {
            AbTestResult::create(['experiment_id' => $experiment->id(), 'variation' => 'variant_a', 'type' => 'hit', 'data' => []]);
        }

        $html = makeWidget()->html();

        // control has a higher conversion rate so it should appear as the leader
        expect($html)->toContain('control');
    });

    it('links each experiment to its show page', function () {
        $experiment = tap(Experiment::make('link-test')->title('Link Test')->published(true))->save();

        $html = makeWidget()->html();

        expect($html)->toContain(cp_route('ab.experiments.show', $experiment->id()));
    });

    it('includes a link to the experiments index', function () {
        $html = makeWidget()->html();

        expect($html)->toContain(cp_route('ab.experiments.index'));
    });
});
