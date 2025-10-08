<?php

namespace Thoughtco\StatamicABTester\Http\Controllers;

use Statamic\Facades\Action;
use Statamic\Http\Controllers\CP\ActionController;
use Thoughtco\StatamicABTester\Facades\Goal;
use Thoughtco\StatamicABTester\Http\Resources\GoalResource;

class GoalActionsController extends ActionController
{
    protected function getSelectedItems($items, $context)
    {
        return Goal::query()->whereIn('id', $items->all())->get();
    }

    protected function getItemData($item, $context): array
    {
        return array_merge((new GoalResource($item))->resolve()['data'], [
            'itemActions' => Action::for($item, $context),
        ]);
    }
}
