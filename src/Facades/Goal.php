<?php

namespace Thoughtco\StatamicABTester\Facades;

use Illuminate\Support\Facades\Facade;
use Thoughtco\StatamicABTester\Contracts\GoalRepository;

class Goal extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GoalRepository::class;
    }
}
