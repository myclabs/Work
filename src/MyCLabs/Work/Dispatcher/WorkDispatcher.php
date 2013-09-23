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
     * Run a task in background.
     *
     * You can use $wait to wait a given time for the task to complete.
     * If the task hasn't finished during this time, $timedout will be called and this method will return.
     * If the task has finished, $completed will be called.
     *
     * @param Task     $task
     * @param int      $wait      Number of seconds to wait for the task to complete. If 0, doesn't wait.
     * @param callable $completed Called (if $wait > 0) when the task has completed.
     * @param callable $timedout  Called (if $wait > 0) if we hit the timeout while waiting.
     * @param callable $errored   Called (if $wait > 0) if the task errors.
     *
     * @return void No results
     */
    public abstract function runBackground(
        Task $task,
        $wait = 0,
        callable $completed = null,
        callable $timedout = null,
        callable $errored = null
    );

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
