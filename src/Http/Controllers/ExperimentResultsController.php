<?php

namespace Thoughtco\StatamicABTester\Http\Controllers;

use Statamic\Http\Controllers\Controller;
use Thoughtco\StatamicABTester\Facades\Experiment;

class ExperimentResultsController extends Controller
{
    public function show($experiment)
    {
        return response(['results' => Experiment::find($experiment)->results()]);
    }
}
