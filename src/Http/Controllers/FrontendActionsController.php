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
            if (($experiment = Experiment::find($source)) && ($variant = $experiment->visitorVariation())) {
                $experiment->recordHit($variant, $data);
            }

            return [];
        }

        if ($type == 'success') {
            Goal::completed($source, $data);

            return [];
        }

        if ($type == 'failure') {
            Goal::failed($source, $data);

            return [];
        }

        return [];
    }
}
