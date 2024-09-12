<?php

namespace Thoughtco\ABTester\Http\Controllers;

use Statamic\Http\Controllers\Controller;
use Thoughtco\ABTester\Facades\Experiment;

class GoalsController extends Controller
{
    public function __invoke($params)
    {
        [$experiment, $variant, $destination] = decrypt($params);

        if (! $experiment = Experiment::find($experiment)) {
            return;
        }

        $experiment->recordCompletedGoal($variant);

        return redirect($destination);
    }
}
