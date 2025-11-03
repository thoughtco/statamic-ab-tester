<?php

namespace Thoughtco\StatamicABTester\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Statamic\Events\Event;
use Thoughtco\StatamicABTester\Contracts\Experiment;

class ExperimentCreating extends Event
{
    use InteractsWithSockets, SerializesModels;

    public function __construct(public Experiment $experiment) {}

    /**
     * Dispatch the event with the given arguments, and halt on first non-null listener response.
     *
     * @return mixed
     */
    public static function dispatch()
    {
        return event(new static(...func_get_args()), [], true);
    }
}
