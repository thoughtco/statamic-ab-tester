<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Statamic\Facades\User;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Models\AbTestResult;

beforeEach(function () {
    $this->actingAs(User::make()->makeSuper()->save());
});

describe('Experiment CSV export', function () {
    it('returns a CSV response', function () {
        $experiment = tap(Experiment::make('export-test')->title('Export Test'))->save();

        $response = $this->get(cp_route('ab.experiments.export', $experiment->id()));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="export-test-results.csv"');
    });

    it('includes the header row', function () {
        $experiment = tap(Experiment::make('export-test')->title('Export Test'))->save();

        $csv = $this->get(cp_route('ab.experiments.export', $experiment->id()))
            ->streamedContent();

        $lines = array_filter(explode("\n", $csv));
        $header = str_getcsv(reset($lines));

        expect($header)->toBe(['id', 'variation', 'type', 'goal_id', 'ip_address', 'user_id', 'created_at', 'data']);
    });

    it('exports one data row per result record', function () {
        $experiment = tap(Experiment::make('export-test')->title('Export Test'))->save();

        AbTestResult::create(['experiment_id' => $experiment->id(), 'variation' => '1', 'type' => 'hit', 'data' => []]);
        AbTestResult::create(['experiment_id' => $experiment->id(), 'variation' => '1', 'type' => 'success', 'data' => []]);
        AbTestResult::create(['experiment_id' => $experiment->id(), 'variation' => '2', 'type' => 'hit', 'data' => []]);

        $csv = $this->get(cp_route('ab.experiments.export', $experiment->id()))
            ->streamedContent();

        $lines = array_values(array_filter(explode("\n", trim($csv))));

        // 1 header + 3 data rows
        expect($lines)->toHaveCount(4);
    });

    it('exports the correct values for each row', function () {
        $experiment = tap(Experiment::make('export-test')->title('Export Test'))->save();

        AbTestResult::create([
            'experiment_id' => $experiment->id(),
            'variation' => '1',
            'type' => 'hit',
            'goal_id' => null,
            'ip_address' => '127.0.0.1',
            'user_id' => null,
            'data' => ['page' => '/home'],
        ]);

        $csv = $this->get(cp_route('ab.experiments.export', $experiment->id()))
            ->streamedContent();

        $lines = array_values(array_filter(explode("\n", trim($csv))));
        $dataRow = str_getcsv($lines[1]);

        expect($dataRow[1])->toBe('1');          // variation
        expect($dataRow[2])->toBe('hit');         // type
        expect($dataRow[3])->toBe('');            // goal_id (null → empty)
        expect($dataRow[4])->toBe('127.0.0.1');   // ip_address
        expect($dataRow[5])->toBe('');            // user_id (null → empty)
        expect(json_decode($dataRow[7], true))->toBe(['page' => '/home']); // data
    });

    it('exports only results for the requested experiment', function () {
        $expA = tap(Experiment::make('exp-a')->title('Exp A'))->save();
        $expB = tap(Experiment::make('exp-b')->title('Exp B'))->save();

        AbTestResult::create(['experiment_id' => $expA->id(), 'variation' => '1', 'type' => 'hit', 'data' => []]);
        AbTestResult::create(['experiment_id' => $expB->id(), 'variation' => '1', 'type' => 'hit', 'data' => []]);
        AbTestResult::create(['experiment_id' => $expB->id(), 'variation' => '2', 'type' => 'hit', 'data' => []]);

        $csv = $this->get(cp_route('ab.experiments.export', $expA->id()))
            ->streamedContent();

        $lines = array_values(array_filter(explode("\n", trim($csv))));

        // 1 header + 1 data row (only expA's result)
        expect($lines)->toHaveCount(2);
    });

    it('exports only the header row when there are no results', function () {
        $experiment = tap(Experiment::make('empty-export')->title('Empty Export'))->save();

        $csv = $this->get(cp_route('ab.experiments.export', $experiment->id()))
            ->streamedContent();

        $lines = array_values(array_filter(explode("\n", trim($csv))));

        expect($lines)->toHaveCount(1);
        expect(str_getcsv($lines[0])[0])->toBe('id');
    });

    it('returns 404 for a non-existent experiment', function () {
        $this->get(cp_route('ab.experiments.export', 'does-not-exist'))
            ->assertNotFound();
    });

    it('slugifies the experiment title in the filename', function () {
        $experiment = tap(Experiment::make('slug-test')->title('My Fancy Experiment'))->save();

        $this->get(cp_route('ab.experiments.export', $experiment->id()))
            ->assertHeader('Content-Disposition', 'attachment; filename="my-fancy-experiment-results.csv"');
    });
});
