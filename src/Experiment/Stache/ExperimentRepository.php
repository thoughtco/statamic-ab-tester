<?php

namespace Thoughtco\StatamicABTester\Experiment\Stache;

use Statamic\Facades\Stache;
use Thoughtco\StatamicABTester\Contracts\Experiment as Contract;
use Thoughtco\StatamicABTester\Contracts\ExperimentQueryBuilder as QueryBuilderContract;
use Thoughtco\StatamicABTester\Experiment\ExperimentRepository as BaseRepository;

class ExperimentRepository extends BaseRepository
{
    public function __construct()
    {
        $this->store = Stache::store('experiments');
    }

    public function save($post)
    {
        if (! $post->handle()) {
            throw new \Exception('`handle` is required');
        }

        $this->store->save($post);
    }

    public function delete($post)
    {
        $this->store->delete($post);
    }

    public function query()
    {
        return new ExperimentQueryBuilder($this->store);
    }

    public static function bindings()
    {
        return [
            Contract::class => Experiment::class,
            QueryBuilderContract::class => ExperimentQueryBuilder::class,
        ];
    }
}
