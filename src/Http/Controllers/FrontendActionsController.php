<?php

namespace Thoughtco\StatamicABTester\Http\Controllers;

use Illuminate\Routing\Controller;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Facades\Goal;

class FrontendActionsController extends Controller
{
    public function run()
    {
        if (! $type = request()->input('type')) {
            return [];
        }

        if (! $source = request()->input('source')) {
            return [];
        }

        $data = request()->input('data', []);

        if ($type == 'hit') {
            Experiment::find($source)?->recordHit($data);

            return [];
        }

        if ($type == 'success') {
            Goal::success($source, $data);

            return [];
        }

        if ($type == 'failure') {
            Goal::failure($source, $data);

            return [];
        }

        return [];
    }
}
