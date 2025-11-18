<?php

namespace Thoughtco\StatamicABTester\Fieldtypes;

use Statamic\Facades\Data;
use Statamic\Fields\Fieldtype;
use Statamic\Support\Arr;

class ExperimentFields extends Fieldtype
{
    protected $component = 'ab_tester_experiment_fields';

    protected $selectable = false;

    public function preload()
    {
        $data = parent::preload() ?? [];

        if (! $parent = $this->field()->parent()) {
            return $data;
        }

        if ($parent->type() != 'item') {
            return $data;
        }

        if (! $item = Data::find($parent->get('item_id'))) {
            return $data;
        }

        $blueprint = $item->blueprint();

        $enabledFields = $blueprint->fields()->all()->filter(fn ($field) => Arr::get($field->config(), 'ab_tester_enable', config('statamic-ab-tester.blueprint_fields_approach') == 'opt-out'))->map->handle()->all();

        return array_merge($data, [
            'abTester' => [
                'meta' => $blueprint->fields()->only($enabledFields)->meta(),
                'fields' => $blueprint->fields()->only($enabledFields)->toPublishArray(),
                'values' => $blueprint->fields()->only($enabledFields)->addValues($item->toArray())->values(),
            ],
        ]);
    }
}
