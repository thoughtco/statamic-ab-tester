<?php

namespace Thoughtco\StatamicABTester\Experiment\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Stache;
use Thoughtco\StatamicABTester\Contracts\Experiment as ExperimentContract;
use Thoughtco\StatamicABTester\Contracts\ExperimentQueryBuilder as QueryBuilderContract;
use Thoughtco\StatamicABTester\Experiment\ExperimentRepository as BaseRepository;

class ExperimentRepository extends BaseRepository
{
    public function save($experiment)
    {
        if (! $experiment->id()) {
            $experiment->id(Stache::generateId());
        }

        $this->toModel($experiment)->save();
    }

    public function delete($experiment)
    {
        $this->toModel($experiment)?->delete();
    }

    public function query()
    {
        return new ExperimentQueryBuilder(ExperimentModel::query());
    }

    public static function fromModel(Model $model)
    {
        return (new self)->make()
            ->id($model->id)
            ->title($model->title)
            ->type($model->type)
            ->goals($model->goals ?? [])
            ->startAt($model->start_at ?? null)
            ->endAt($model->end_at ?? null)
            ->published($model->published ?? false)
            ->completedAt($model->completed_at ?? null)
            ->data($model->data);
    }

    private function toModel(ExperimentContract $experiment)
    {
        return ExperimentModel::updateOrCreate([
            'id' => $experiment->id(),
        ], [
            'title' => $experiment->title(),
            'type' => $experiment->type(),
            'goals' => $experiment->goals(),
            'start_at' => $experiment->startAt(),
            'end_at' => $experiment->endAt(),
            'completed_at' => $experiment->completedAt(),
            'published' => $experiment->published(),
            'data' => $experiment->data()->all(),
        ]);
    }

    public static function bindings()
    {
        return [
            ExperimentContract::class => Experiment::class,
            QueryBuilderContract::class => ExperimentQueryBuilder::class,
        ];
    }
}
