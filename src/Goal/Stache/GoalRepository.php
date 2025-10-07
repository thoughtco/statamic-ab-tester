<?php

namespace Thoughtco\StatamicABTester\Goal\Stache;

use Statamic\Facades\Stache;
use Thoughtco\StatamicABTester\Contracts\Goal as Contract;
use Thoughtco\StatamicABTester\Contracts\GoalQueryBuilder as QueryBuilderContract;
use Thoughtco\StatamicABTester\Goal\GoalRepository as BaseRepository;

class GoalRepository extends BaseRepository
{
    protected $store;

    public function __construct()
    {
        $this->store = Stache::store('goals');
    }

    public function save($entry)
    {
        if (! $entry->handle()) {
            throw new \Exception('`handle` is required');
        }

        $this->store->save($entry);
    }

    public function delete($entry)
    {
        $this->store->delete($entry);
    }

    public function query()
    {
        return new GoalQueryBuilder($this->store);
    }

    public static function bindings()
    {
        return [
            Contract::class => Goal::class,
            QueryBuilderContract::class => GoalQueryBuilder::class,
        ];
    }
}
