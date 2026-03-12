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
                            [
                                'handle' => 'weight',
                                'field' => [
                                    'label' => __('Weight'),
                                    'type' => 'integer',
                                    'default' => 50,
                                    'instructions' => __('Relative weight for traffic distribution. Leave equal for an even split.'),
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
                    'traffic_split' => [
                        'type' => 'integer',
                        'label' => __('Traffic split (% to control)'),
                        'default' => 50,
                        'instructions' => __('Percentage of visitors shown the original (control). The remainder see the variant. Default is 50/50.'),
                        'validate' => 'nullable|integer|min:0|max:100',
                        'if' => [
                            'type' => 'equals item',
                        ],
                    ],
                    'start_at' => [
                        'type' => 'date',
                        'label' => __('Start at'),
                        'time_enabled' => true,
                    ],
                    'end_at' => [
                        'type' => 'date',
                        'label' => __('End at'),
                        'time_enabled' => true,
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
