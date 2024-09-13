<?php

namespace Thoughtco\StatamicABTester\Experiment\Stache;

use Statamic\Data\DataCollection;
use Statamic\Stache\Query\Builder;
use Thoughtco\StatamicABTester\Contracts\ExperimentQueryBuilder as Contract;

class ExperimentQueryBuilder extends Builder implements Contract
{
    protected function collect($items = [])
    {
        return DataCollection::make($items);
    }

    protected function getFilteredKeys()
    {
        if (empty($this->wheres)) {
            return $this->store->paths()->keys();
        }

        return $this->getKeysFromWheres($this->wheres);
    }

    protected function getKeysFromWheres($wheres)
    {
        return collect($wheres)->reduce(function ($ids, $where) {
            $keys = $where['type'] == 'Nested'
                ? $this->getKeysFromWheres($where['query']->wheres)
                : $this->getKeysFromWhere($where);

            return $this->intersectKeysFromWhereClause($ids, $keys, $where);
        });
    }

    protected function getKeysFromWhere($where)
    {
        $items = $this->store
            ->index($where['column'])
            ->items()
            ->mapWithKeys(function ($item, $key) {
                return [$key => $item];
            });

        $method = 'filterWhere'.$where['type'];

        return $this->{$method}($items, $where)->keys();
    }

    protected function getOrderKeyValuesByIndex()
    {
        return collect($this->orderBys)->mapWithKeys(function ($orderBy) {
            $items = $this->store->index($orderBy->sort)
                ->items()
                ->mapWithKeys(function ($item, $key) {
                    return [$key => $item];
                })->all();

            return [$orderBy->sort => $items];
        });
    }
}
