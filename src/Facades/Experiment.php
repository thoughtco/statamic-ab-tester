<?php

namespace Thoughtco\StatamicABTester\Facades;

use Illuminate\Support\Facades\Facade;

class Experiment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Thoughtco\StatamicABTester\Experiment::class;
    }
}
