<?php

namespace Thoughtco\StatamicABTester\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\Action;
use Statamic\Facades\User;

class ExperimentResource extends JsonResource
{
    protected $columns;

    public function columns($columns): self
    {
        $this->columns = $columns;

        return $this;
    }

    public function toArray($request): array
    {
        $experiment = $this->resource;

        return [
            'id' => $experiment->id(),
            'title' => $experiment->title(),
            'edit_url' => cp_route('ab.experiments.edit', $experiment->id()),
            'editable' => User::current()->can('edit a/b experiments'),
            'viewable' => User::current()->can('view a/b experiments'),
            'actions' => Action::for($experiment, []),
        ];
    }
}
