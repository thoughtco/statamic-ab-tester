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

    public function blueprint($editing = false)
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
                        'options' => collect([
                            ['value' => __('Item'), 'key' => 'item'],
                            ['value' => __('Manual'), 'key' => 'manual'],
                        ])->filter(fn ($option) => (! $editing) && ($option['key'] == 'item') ? false : true)->values()->all(),
                        'max_items' => 1,
                        'default' => 'manual',
                        'visibility' => $editing ? 'read_only' : 'visible',
                    ],
                    'experiment_fields' => [
                        'type' => 'experiment_fields',
                        'hide_display' => true,
                        'if' => [
                            'type' => 'equals item',
                        ],
                    ],
                    'manual_fields' => [
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
                                'handle' => 'handle',
                                'field' => [
                                    'label' => __('Slug'),
                                    'type' => 'slug',
                                    'validate' => 'required',
                                ],
                            ],
                        ],
                        'validate' => 'array',
                        'if' => [
                            'type' => 'equals manual',
                        ],
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
