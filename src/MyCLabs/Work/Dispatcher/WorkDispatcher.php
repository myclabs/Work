<?php

namespace MyCLabs\Work\Dispatcher;

use MyCLabs\Work\Event\DispatcherEventListener;
use MyCLabs\Work\Task\Task;

/**
 * Dispatch tasks.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface WorkDispatcher
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
     * Run a task in background (asynchronously).
     *
     * @param Task $task
     *
     * @return void No results given the task is run asynchronously.
     */
    public function run(Task $task);

    /**
     * @param DispatcherEventListener $listener
     */
    public function registerEventListener(DispatcherEventListener $listener);
}
