<?php

namespace Thoughtco\ABTester\Facades;

use Illuminate\Support\Facades\Facade;

class Experiment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Thoughtco\ABTester\Experiment::class;
    }
}
