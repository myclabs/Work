<?php

namespace MyCLabs\Work\Dispatcher;

use MyCLabs\Work\Dispatcher\Event\DispatcherEventListener;

/**
 * Provides event methods for a work dispatcher.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
trait WorkDispatcherEventTrait
{
    /**
     * @var DispatcherEventListener[]
     */
    protected $listeners;

    /**
     * @param DispatcherEventListener $listener
     */
    public function registerEventListener(DispatcherEventListener $listener)
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
