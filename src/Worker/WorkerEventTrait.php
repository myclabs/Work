<?php

namespace MyCLabs\Work\Worker;

use MyCLabs\Work\Worker\Event\WorkerEventListener;

/**
 * Provides event methods for a work dispatcher.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
trait WorkerEventTrait
{
    /**
     * @var WorkerEventListener[]
     */
    protected $listeners = [];

    /**
     * @param WorkerEventListener $listener
     */
    public function registerEventListener(WorkerEventListener $listener)
    {
        $this->listeners[] = $listener;
    }

    /**
     * Dispatch an event to all the listeners.
     *
     * @param string $event
     * @param array  $parameters
     */
    protected function triggerEvent($event, array $parameters = [])
    {
        foreach ($this->listeners as $listener) {
            call_user_func_array([$listener, $event], $parameters);
        }
    }
}
