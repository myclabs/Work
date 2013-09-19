<?php

namespace MyCLabs\Work\Dispatcher;

use MyCLabs\Work\EventListener;
use MyCLabs\Work\Task\Task;

/**
 * Dispatch tasks.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
abstract class WorkDispatcher
{
    /**
     * Event: before a task is dispatched to be run by a worker.
     */
    const EVENT_BEFORE_TASK_DISPATCHED = 'beforeTaskDispatched';

    /**
     * Event: before a task is serialized.
     */
    const EVENT_BEFORE_TASK_SERIALIZATION = 'beforeTaskSerialization';

    /**
     * @var EventListener[]
     */
    private $listeners = [];

    /**
     * Run a task in background
     *
     * @param Task $task
     * @return void No results
     */
    public abstract function runBackground(Task $task);

    /**
     * @param EventListener $listener
     */
    public function addEventListener(EventListener $listener)
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
