<?php

namespace Thoughtco\StatamicABTester\Http\Middleware;

use Closure;
use Illuminate\Support\Collection;
use Statamic\Contracts\Entries\Entry;
use Statamic\Entries\AugmentedEntry;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Structures\AugmentedPage;
use Statamic\Structures\Page;
use Statamic\Support\Arr;
use Thoughtco\StatamicABTester\Facades\Experiment;

class ABTesterMiddleware
{
    public $abTesterExperiments = [];

    public function handle($request, Closure $next)
    {
        $experiments = Experiment::query()
            ->where('published', true)
            ->whereNull('completed_at')
            ->where('type', 'item')
            ->where(fn ($query) => $query->whereNull('start_at')->orWhere('start_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('end_at')->orWhere('end_at', '>=', now()))
            ->get()
            ->keyBy(fn ($experiment) => $experiment->get('item_id'));

        if ($experiments->isEmpty()) {
            return $next($request);
        }

        $this->setupAugmentationHooks($experiments);

        $response = $next($request);

        $response->headers->set('X-ABTester-Experiments', collect($this->abTesterExperiments)->filter()->unique()->sort()->join(','));

        return $response;
    }

    private function setupAugmentationHooks(Collection $experiments)
    {
        $self = $this;

        $alreadyAugmented = collect();

        $augmentFunction = function ($augmented, $next) use ($alreadyAugmented, $experiments, $self) {
            $item = $augmented;

            if ($augmented instanceof AugmentedPage) {
                if (! $item = $this->entry()) {
                    return $next($augmented);
                }
            }

            $id = $item instanceof AugmentedEntry ? $item->get('id')->raw() : $item->id;

            if (! $experiment = $experiments->get($id)) {
                return $next($augmented);
            }

            if ($already = $alreadyAugmented->get($id)) {
                return $next($already);
            }

            $variant = null;

            if ($experiment->type() == 'item') {
                $variant = $experiment->chooseVariation();

                $experiment->recordHit($variant);

                $augmented = $self->populateVariationForItemExperiment(
                    experiment: $experiment,
                    item: $item,
                    variant: $variant
                );

                $alreadyAugmented->put($id, $augmented);
            }

            if ($variant !== null) {
                $self->abTesterExperiments[] = $experiment->id().':'.$variant;

                session()->put('statamic.ab.'.$experiment->id(), $variant);
            }

            return $next($augmented);
        };

        Page::hook('augmented', $augmentFunction);
        app(Entry::class)::hook('augmented', $augmentFunction);

        return $this;
    }

    public function populateVariationForItemExperiment($experiment, $item, $variant)
    {
        if ($variant == 1) {
            return $item;
        }

        $values = Arr::get($experiment->get('experiment_fields'), 'values', []);

        $entry = $item;

        if ($item instanceof AugmentedEntry) {
            $entry = EntryFacade::find($item->get('id')->raw());
        }

        // $data is private, so :shrug:
        $reflection = new \ReflectionClass($entry);
        $property = $reflection->getProperty('data');
        $property->setAccessible(true);
        $property->setValue($entry, $entry->data()->merge($values));

        if ($item instanceof AugmentedEntry) {
            $entry = new AugmentedEntry($entry);
        }

        return $entry;
    }
}
