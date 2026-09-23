<?php

use Illuminate\Cache\Repository;
use Thoughtco\StatamicABTester\StaticCaching\ABCacher;
use Thoughtco\StatamicABTester\Tests\TestCase;

uses(TestCase::class);

describe('AB Cacher', function () {
    it('extends half caching', function () {
        $cacher = new ABCacher(app(Repository::class), []);

        expect($cacher)->toBeInstanceOf(ABCacher::class);
    });
});
