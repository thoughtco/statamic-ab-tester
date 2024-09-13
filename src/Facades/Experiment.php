<?php

namespace Thoughtco\StatamicABTester\Facades;

use Illuminate\Support\Facades\Facade;
use Thoughtco\StatamicABTester\Contracts\ExperimentRepository;

class Experiment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ExperimentRepository::class;
    }
}
