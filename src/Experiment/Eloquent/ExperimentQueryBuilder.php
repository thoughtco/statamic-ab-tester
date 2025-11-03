<?php

namespace Thoughtco\StatamicABTester\Experiment\Eloquent;

use Illuminate\Support\Str;
use Statamic\Data\DataCollection;
use Statamic\Query\EloquentQueryBuilder;
use Thoughtco\StatamicABTester\Contracts\ExperimentQueryBuilder as Contract;

class ExperimentQueryBuilder extends EloquentQueryBuilder implements Contract
{
    const COLUMNS = [
        'id',
        'completed_at',
        'data',
        'end_at',
        'goals',
        'experiment_fields',
        'manual_fields',
        'published',
        'start_at',
        'title',
        'type',
        'created_at',
        'updated_at',
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
            return ExperimentRepository::fromModel($model);
        });
    }
}
