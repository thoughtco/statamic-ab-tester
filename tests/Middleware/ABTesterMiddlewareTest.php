<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Illuminate\Http\Request;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Http\Middleware\ABTesterMiddleware;

describe('AB Tester Middleware', function () {
    it('processes request without experiments', function () {
        $middleware = new ABTesterMiddleware;
        $request = Request::create('/');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect($response->getContent())->toBe('OK');
    });

    it('handles requests with active experiments', function () {
        $experiment = Experiment::make('test-experiment')
            ->title('Test Experiment')
            ->type('manual')
            ->published(true)
            ->data([
                'manual_fields' => [['handle' => 'variant_a', 'label' => 'Variant A']],
            ])
            ->save();

        $middleware = new ABTesterMiddleware;
        $request = Request::create('/');

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        expect($response->getContent())->toBe('OK');
    });

});
