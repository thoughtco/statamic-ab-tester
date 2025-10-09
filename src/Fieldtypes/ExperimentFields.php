<?php

namespace Thoughtco\StatamicABTester\Fieldtypes;

use Statamic\Facades\Data;
use Statamic\Fields\Fieldtype;

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

        if ($parent->type() != 'entry') {
            return $data;
        }

        if (! $item = Data::find($parent->get('item_id', 'home'))) { // @TODO: remove 'home' fallback
            return $data;
        }

        $blueprint = $item->blueprint();

        return array_merge($data, [
            'abTester' => [
                'meta' => $blueprint->fields()->meta(),
                'fields' => $blueprint->fields()->toPublishArray(),
                'values' => $blueprint->fields()->addValues($item->toArray())->values(),
            ],
        ]);
    }
}
