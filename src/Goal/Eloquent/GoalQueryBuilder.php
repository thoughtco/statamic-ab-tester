<?php

namespace Thoughtco\StatamicABTester\Goal\Eloquent;

use Illuminate\Support\Str;
use Statamic\Data\DataCollection;
use Statamic\Query\EloquentQueryBuilder;
use Thoughtco\StatamicABTester\Contracts\GoalQueryBuilder as Contract;

class GoalQueryBuilder extends EloquentQueryBuilder implements Contract
{
    const COLUMNS = [
        'id', 'handle', 'data', 'title', 'created_at', 'updated_at',
    ];

    protected function column($column)
    {
        if (! in_array($column, self::COLUMNS)) {
            if (! Str::startsWith($column, 'data->')) {
                $column = 'data->'.$column;
            }
        }

        return $column;
    }

    protected function transform($items, $columns = [])
    {
        return DataCollection::make($items)->map(function ($model) {
            return GoalRepository::fromModel($model);
        });
    }
}
