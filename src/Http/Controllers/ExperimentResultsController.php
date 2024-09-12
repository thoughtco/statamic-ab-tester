<?php

namespace Thoughtco\ABTester\Http\Controllers;

use Statamic\Http\Controllers\Controller;
use Thoughtco\ABTester\Facades\Experiment;

class ExperimentResultsController extends Controller
{
    public function show($experiment)
    {
        return response(['results' => Experiment::find($experiment)->results()]);
    }
}
