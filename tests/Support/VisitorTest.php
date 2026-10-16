<?php

use Illuminate\Support\Facades\Cookie;
use Statamic\Support\Str;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Facades\Goal;
use Thoughtco\StatamicABTester\Models\AbTestResult;
use Thoughtco\StatamicABTester\Support\Visitor;
use Thoughtco\StatamicABTester\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    session()->flush();

    $this->goal = tap(Goal::make()->handle('basket')->title('Add to basket'))->save();

    $this->experiment = tap(Experiment::make()
        ->title('Button Test')
        ->type('manual')
        ->published(true)
        ->goals([$this->goal->id()])
        ->data([
            'manual_fields' => [
                ['handle' => 'control', 'weight' => 50],
                ['handle' => 'red_button', 'weight' => 50],
            ],
        ]))
        ->save();
});

describe('Visitor', function () {
    it('keeps the same id for the length of the session', function () {
        $id = Visitor::id();

        expect(Str::isUuid($id))->toBeTrue();
        expect(Visitor::id())->toBe($id);
    });

    it('gives a new session a new id', function () {
        $id = Visitor::id();

        session()->flush();

        expect(Visitor::id())->not->toBe($id);
    });

    it('does not set a cookie by default', function () {
        Visitor::id();

        expect(Cookie::getQueuedCookies())->toBeEmpty();
    });

    it('stores the id in a cookie when one is configured', function () {
        config(['statamic-ab-tester.visitor.cookie' => 'ab_visitor']);

        $id = Visitor::id();

        expect(Cookie::queued('ab_visitor')->getValue())->toBe($id);
    });

    it('restores the id from the cookie in a new session', function () {
        config(['statamic-ab-tester.visitor.cookie' => 'ab_visitor']);

        $id = (string) Str::uuid();
        request()->cookies->set('ab_visitor', $id);

        expect(Visitor::id())->toBe($id);
    });

    it('ignores a cookie that is not a valid id', function () {
        config(['statamic-ab-tester.visitor.cookie' => 'ab_visitor']);

        request()->cookies->set('ab_visitor', 'not-a-uuid');

        expect(Visitor::id())->not->toBe('not-a-uuid');
    });
});

describe('Visitor results', function () {
    it('stores the visitor id on results', function () {
        $this->experiment->recordHit('control');

        expect(AbTestResult::first()->visitor_id)->toBe(Visitor::id());
    });

    it('records one hit per visitor', function () {
        $this->experiment->recordHit('control');
        $this->experiment->recordHit('control');

        session()->flush();

        $this->experiment->recordHit('control');

        expect(AbTestResult::where('type', 'hit')->count())->toBe(2);
    });

    it('keeps a returning visitor on the variant they first saw', function () {
        config(['statamic-ab-tester.visitor.cookie' => 'ab_visitor']);

        $this->experiment->recordHit('red_button');
        $id = Visitor::id();

        // the session expires, but the visitor comes back with their cookie
        session()->flush();
        request()->cookies->set('ab_visitor', $id);

        foreach (range(1, 20) as $i) {
            expect($this->experiment->chooseVariation())->toBe('red_button');
        }
    });

    it('credits a returning visitor\'s conversion to the variant they first saw', function () {
        config(['statamic-ab-tester.visitor.cookie' => 'ab_visitor']);

        $this->experiment->recordHit('red_button');
        $id = Visitor::id();

        session()->flush();
        request()->cookies->set('ab_visitor', $id);

        Goal::completed('basket');

        $this->assertDatabaseHas('ab_test_results', [
            'experiment_id' => $this->experiment->id(),
            'type' => 'success',
            'variation' => 'red_button',
            'visitor_id' => $id,
        ]);
    });

    it('does not credit a conversion from a visitor who never saw the experiment', function () {
        Goal::completed('basket');

        expect(AbTestResult::count())->toBe(0);
    });
});
