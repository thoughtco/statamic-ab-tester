<?php

namespace Thoughtco\StatamicABTester\Http\Controllers;

use Statamic\Facades\Action;
use Statamic\Http\Controllers\CP\ActionController;
use Thoughtco\StatamicABTester\Facades\Experiment;
use Thoughtco\StatamicABTester\Http\Resources\ExperimentResource;

class ExperimentActionsController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return Experiment::query()->whereIn('id', $items->all())->get();
    }

    protected function getItemData($item, $context): array
    {
        return array_merge((new ExperimentResource($item))->resolve()['data'], [
            'itemActions' => Action::for($item, $context),
        ]);
    }
}
