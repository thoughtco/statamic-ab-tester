<?php

namespace Thoughtco\StatamicABTester\Goal\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Stache;
use Thoughtco\StatamicABTester\Contracts\Goal as GoalContract;
use Thoughtco\StatamicABTester\Contracts\GoalQueryBuilder as QueryBuilderContract;
use Thoughtco\StatamicABTester\Goal\GoalRepository as BaseRepository;

class GoalRepository extends BaseRepository
{
    public function save($goal)
    {
        if (! $goal->id()) {
            $goal->id(Stache::generateId());
        }

        $this->toModel($goal)->save();
    }

    public function delete($goal)
    {
        $this->toModel($goal)?->delete();
    }

    public function query()
    {
        return new GoalQueryBuilder(GoalModel::query());
    }

    public static function fromModel(Model $model)
    {
        return (new self)->make()
            ->id($model->id)
            ->handle($model->handle)
            ->title($model->title)
            ->data($model->data);
    }

    private function toModel(GoalContract $goal)
    {
        return GoalModel::updateOrCreate([
            'id' => $goal->id(),
        ], [
            'title' => $goal->title(),
            'data' => $goal->data(),
            'handle' => $goal->handle(),
        ]);
    }

    public static function bindings()
    {
        return [
            GoalContract::class => Goal::class,
            QueryBuilderContract::class => GoalQueryBuilder::class,
        ];
    }
}
