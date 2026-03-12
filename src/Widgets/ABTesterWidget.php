<?php

namespace Thoughtco\StatamicABTester\Widgets;

use Illuminate\Support\Facades\DB;
use Statamic\Widgets\Widget;
use Thoughtco\StatamicABTester\Facades\Experiment;

class ABTesterWidget extends Widget
{
    protected static $handle = 'ab_tester';

    public function html()
    {
        $activeExperiments = Experiment::query()
            ->whereNull('completed_at')
            ->where('published', true)
            ->where(fn ($q) => $q->whereNull('start_at')->orWhere('start_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', now()))
            ->get();

        $experiments = $activeExperiments->map(function ($experiment) {
            $variantResults = $experiment->resultsQuery()
                ->select('variation', DB::raw('count(*) as total'))
                ->groupBy('variation')
                ->get()
                ->map(function ($row) use ($experiment) {
                    $successes = $experiment->resultsQuery()
                        ->where('variation', $row->variation)
                        ->where('type', 'success')
                        ->count();

                    return [
                        'label' => $row->variation,
                        'hits' => $row->total,
                        'rate' => $row->total > 0 ? round($successes / $row->total * 100, 1) : 0,
                    ];
                });

            $leader = $variantResults->sortByDesc('rate')->first();

            return [
                'title' => $experiment->title(),
                'url' => cp_route('ab.experiments.show', $experiment->id()),
                'total_hits' => $variantResults->sum('hits'),
                'leader_label' => $leader ? $leader['label'] : null,
                'leader_rate' => $leader ? $leader['rate'] : null,
            ];
        });

        return view('ab::widgets.ab-tester', [
            'activeCount' => $activeExperiments->count(),
            'experiments' => $experiments,
            'indexUrl' => cp_route('ab.experiments.index'),
        ])->render();
    }
}
