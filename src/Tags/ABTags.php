<?php

namespace Thoughtco\StatamicABTester\Tags;

use Statamic\Support\Str;
use Statamic\Tags\Tags;
use Thoughtco\StatamicABTester\Facades\Experiment;

class ABTags extends Tags
{
    protected static $handle = 'ab';

    public static $jsHasBeenRendered = false;

    public function index()
    {
        if (! $experimentId = $this->params->pull('experiment')) {
            return $this->parse();
        }

        if (! $experiment = Experiment::query()
            ->where('id', $experimentId)
            ->where('published', true)
            ->where(fn ($query) => $query->whereNull('start_at')->orWhere('start_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('end_at')->orWhere('end_at', '>=', now()))
            ->first()) {
            return $this->parse();
        }

        $useSession = $this->params->pull('from_session', false);
        if (! $variant = $experiment->chooseVariation(fromSession: $useSession)) {
            return $this->parse();
        }

        $experiment->recordHit($variant, $this->params->all());

        if ($useSession) {
            session()->put('statamic.ab.'.$experimentId, $variant);
        }

        return $this->parse([
            'experiment' => $experiment,
            'variant' => $variant,
        ]);
    }

    public function js()
    {
        if (static::$jsHasBeenRendered) {
            return;
        }

        static::$jsHasBeenRendered = true;

        return "
        <script>
        const abTester = {
            hit: (experiment, data) => abTester.run('hit', experiment, data),
            completed: (goal, data) => abTester.run('success', goal, data),
            failure: (goal, data) => abTester.run('failure', goal, data),

            run: (type, source, data) => {
                fetch('".route('statamic.ab-tester.front-end-js')."', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '".csrf_token()."',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        type: type,
                        source: source,
                        data: data,
                    })
                });
            }
        }
        </script>
        ";
    }

    public function failure()
    {
        if (! $experimentId = $this->params->pull('experiment')) {
            return $this->parse();
        }

        $variantHandle = false;
        if ($this->params->bool('from_session')) {
            if (! $variantHandle = session()->get('statamic.ab.'.$experimentId)) {
                return $this->parse();
            }
        }

        if (! $variantHandle) {
            if (! $variantHandle = $this->params->pull('variant')) {
                return $this->parse();
            }
        }

        if (! $experiment = Experiment::find($experimentId)) {
            return $this->parse();
        }

        $experiment->recordFailure($variantHandle, $params->pull('goal'), $params->all());

        if (! $this->isPair) {
            return;
        }

        return $this->parse([
            'error' => false,
        ]);
    }

    public function completed()
    {
        if (! $experimentId = $this->params->pull('experiment')) {
            return $this->parse();
        }

        $variantHandle = false;
        if ($this->params->bool('from_session')) {
            if (! $variantHandle = session()->get('statamic.ab.'.$experimentId)) {
                return $this->parse();
            }
        }

        if (! $variantHandle) {
            if (! $variantHandle = $this->params->pull('variant')) {
                return $this->parse();
            }
        }

        if (! $experiment = Experiment::find($experimentId)) {
            return $this->parse();
        }

        $experiment->recordSuccess($variantHandle, $params->pull('goal'), $params->all());

        if (! $this->isPair) {
            return;
        }

        return $this->parse([
            'error' => false,
        ]);
    }

    public function wildcard($tag)
    {
        if (! Str::contains($tag, ':')) {
            return;
        }

        if (Str::before($tag, ':') == 'goal') {
            if (! $handle = $this->params->pull('handle')) {
                return;
            }

            $html = '';
            if (! static::$jsHasBeenRendered) {
                $html = $this->js();
            }

            $params = $this->params->all();

            match (Str::after($tag, ':')) {
                'completed' => $html .= '<script>abTester.completed("'.$handle.'", '.json_encode($params).');</script>',
                'failed' => $html .= '<script>abTester.failed("'.$handle.'", '.json_encode($params).');</script>',
                default => false
            };

            return $html;
        }
    }
}
