<?php

namespace Thoughtco\StatamicABTester\Goal;

use Statamic\Data\DataCollection;
use Statamic\Facades\Blueprint;
use Thoughtco\StatamicABTester\Contracts\Goal as GoalContract;
use Thoughtco\StatamicABTester\Contracts\GoalRepository as RepositoryContract;

abstract class GoalRepository implements RepositoryContract
{
    public function all(): DataCollection
    {
        return $this->query()->get();
    }

    public function find($id): ?GoalContract
    {
        return $this->query()->where('id', $id)->first();
    }

    public function make(): GoalContract
    {
        return app(GoalContract::class);
    }

    public static function bindings()
    {
        return [];
    }

    public function blueprint()
    {
        return Blueprint::makeFromFields([
            'title' => [
                'type' => 'text',
                'validate' => 'required',
            ],
            'handle' => [
                'type' => 'slug',
                'validate' => 'required',
            ],
        ]);
    }
}
