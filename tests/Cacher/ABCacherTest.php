<?php

uses(\Thoughtco\StatamicABTester\Tests\TestCase::class);

use Illuminate\Cache\Repository;
use Thoughtco\StatamicABTester\StaticCaching\ABCacher;

describe('AB Cacher', function () {
    it('extends half caching', function () {
        $cacher = new ABCacher(app(Repository::class), []);

        expect($cacher)->toBeInstanceOf(ABCacher::class);
    });
});
