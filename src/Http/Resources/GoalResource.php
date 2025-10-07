<?php

namespace Thoughtco\StatamicABTester\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Facades\User;

class GoalResource extends JsonResource
{
    protected $columns;

    public function columns($columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function toArray($request): array
    {
        $goal = $this->resource;

        return [
            'handle' => $goal->handle(),
            'title' => $goal->title(),
            'edit_url' => cp_route('ab.goals.edit', $goal->handle()),
            'editable' => User::current()->can('edit a/b goals'),
            'viewable' => User::current()->can('view a/b goals'),
            'actions' => Action::for($goal, []),
        ];
    }
}
