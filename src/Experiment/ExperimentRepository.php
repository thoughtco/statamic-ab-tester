<?php

namespace Thoughtco\StatamicABTester\Experiment;

use Statamic\Data\DataCollection;
use Statamic\Facades\Blueprint;
use Thoughtco\StatamicABTester\Contracts\Experiment as ExperimentContract;
use Thoughtco\StatamicABTester\Contracts\ExperimentRepository as RepositoryContract;

abstract class ExperimentRepository implements RepositoryContract
{
    public function all(): DataCollection
    {
        return $this->query()->get();
    }

    public function find($id): ?ExperimentContract
    {
        return $this->query()->where('handle', $id)->first();
    }

    public function make(): ExperimentContract
    {
        return app(ExperimentContract::class);
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
                'validate' => ['required', 'alpha_dash'],
                'from' => 'title',
            ],
            'type' => [
                'type' => 'select',
                'validate' => 'required',
                'options' => [
                    'entry' => 'Entry',
                    'manual' => 'Manual',
                ],
                'max_items' => 1,
            ],
            'variants' => [
                'type' => 'grid',
                'mode' => 'stacked',
                'fields' => [
                    [
                        'handle' => 'label',
                        'field' => [
                            'label' => __('Label'),
                            'type' => 'text',
                            'validate' => 'required',
                        ],
                    ],
                    [
                        'handle' => 'entry',
                        'field' => [
                            'label' => __('Entry'),
                            'type' => 'entries',
                            'mode' => 'default',
                            'max_items' => 1,
                            'if' => [
                                'root.type' => 'equals entry',
                            ],
                        ],
                    ],
                ],
                'validate' => 'array',
            ],
            'start_at' => [
                'type' => 'date',
                'label' => __('Start at'),
                'time_enabled' => true,
                'validate' => 'required',
            ],
            'end_at' => [
                'type' => 'date',
                'label' => __('End at'),
                'time_enabled' => true,
                'validate' => 'required',
            ]
        ]);
    }
}
