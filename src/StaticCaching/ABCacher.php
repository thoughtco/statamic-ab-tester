<?php

namespace Thoughtco\StatamicABTester\StaticCaching;

use Illuminate\Http\Request;
use Statamic\StaticCaching\Cachers\ApplicationCacher;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Thoughtco\StatamicABTester\Facades\Experiment;

class ABCacher extends ApplicationCacher
{
    private $variationCacheKey = false;

    private function getFromCache(Request $request)
    {
        $url = $this->getUrl($request);

        $key = $this->variationCacheKey == false ? $this->makeHash($url) : $this->variationCacheKey;

        return $this->cache->get($this->normalizeKey('responses:'.$key));
    }

    protected function makeHash($url, $allowVariationKey = true)
    {
        return $allowVariationKey && $this->variationCacheKey ?: md5($url);
    }

    public function hasCachedPage(Request $request)
    {
        if (! $cachedPage = $this->getFromCache($request)) {
            return false;
        }

        if (! $header = Arr::get($cachedPage['headers'], 'x-abtester-experiments')) {
            return (bool) $this->cached = $cachedPage;
        }

        if (is_array($header)) {
            $header = array_shift($header);
        }

        $cachedExperiments = collect(explode(',', $header))
            ->unique()
            ->mapWithKeys(fn ($header) => [Str::before($header, ':') => Str::after($header, ':')])
            ->sort();

        $requestExperiments = $cachedExperiments
            ->map(function ($variation, $experimentId) {
                if (! $experiment = Experiment::find($experimentId)) {
                    return;
                }

                $variation = $experiment->chooseVariation();

                // if we haven't already hit this experiment, then log a hit
                if (! session()->get('statamic.ab.'.$experiment->id())) {
                    session()->put('statamic.ab.'.$experiment->id(), $variation);

                    $experiment->recordHit($variant);
                }

                return $variation;
            })
            ->filter();

        $requestExperimentsAsHeader = $requestExperiments->map(fn ($v, $e) => $e.':'.$v)->join(',');

        if ($cachedExperiments->map(fn ($v, $e) => $e.':'.$v)->join(',') === $requestExperimentsAsHeader) {
            return true;
        }

        $url = $this->getUrl($request);

        $key = $this->makeHash($url, false);

        $this->variationCacheKey = $key.':'.md5($requestExperimentsAsHeader);

        if ($this->cache->get($this->normalizeKey('responses:'.$this->variationCacheKey))) {
            return true;
        }

        return false;
    }
}
