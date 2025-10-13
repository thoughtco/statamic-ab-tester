<?php

namespace Thoughtco\StatamicABTester\Experiment;

use Statamic\Data\DataCollection;
use Statamic\Facades\Blueprint;
use Thoughtco\StatamicABTester\Contracts\Experiment as ExperimentContract;
use Thoughtco\StatamicABTester\Contracts\ExperimentRepository as RepositoryContract;
use Thoughtco\StatamicABTester\Facades\Goal;

abstract class ExperimentRepository implements RepositoryContract
{
    public function all(): DataCollection
    {
        return $this->query()->get();
    }

    public function find($id): ?ExperimentContract
    {
        return $this->query()->where('id', $id)->first();
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
        return Blueprint::makeFromTabs([
            'main' => [
                'display' => 'Main',
                'fields' => [
                    'title' => [
                        'type' => 'text',
                        'validate' => 'required',
                    ],
                    'type' => [
                        'type' => 'select',
                        'validate' => 'required',
                        'options' => [
                            ['value' => 'Item', 'key' => 'item'],
                        ],
                        'max_items' => 1,
                        'default' => 'entry',
                    ],
                    'experiment_fields' => [
                        'type' => 'experiment_fields',
                        'hide_display' => true,
                    ],
                    'goals' => [
                        'type' => 'select',
                        'validate' => 'required',
                        'options' => Goal::all()->map(fn ($goal) => ['value' => $goal->title(), 'key' => $goal->id()])->all(),
                        'multiple' => true,
                    ],
                ],
            ],
            'sidebar' => [
                'fields' => [
                    'start_at' => [
                        'type' => 'date',
                        'label' => __('Start at'),
                        'time_enabled' => true,
                        'validate' => 'nullable,date_format:Y-m-d H:i:s',
                    ],
                    'end_at' => [
                        'type' => 'date',
                        'label' => __('End at'),
                        'time_enabled' => true,
                        'validate' => 'nullable,date_format:Y-m-d H:i:s',
                    ],
                    'published' => [
                        'type' => 'toggle',
                        'label' => __('Published'),
                        'default' => true,
                    ],
                ],
            ],
        ]);
    }
}
