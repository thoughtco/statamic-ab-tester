<?php

namespace Thoughtco\StatamicABTester\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Statamic\Contracts\Git\ProvidesCommitMessage;
use Statamic\Events\Event;

class ExperimentDeleting extends Event implements ProvidesCommitMessage
{
    use InteractsWithSockets, SerializesModels;

    public $experiment;

    public function __construct($experiment)
    {
        $this->experiment = $experiment;
    }

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
