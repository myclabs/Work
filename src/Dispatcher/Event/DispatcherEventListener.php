<?php

namespace MyCLabs\Work\Dispatcher\Event;

use MyCLabs\Work\Task\Task;

/**
 * Interface implementing a listener for work dispatcher events.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface DispatcherEventListener
{
    /**
     * Event called before a task is dispatched to be run by a worker.
     *
     * You can use this event to prepare the task to be sent.
     *
     * @param Task $task
     */
    public function beforeTaskDispatched(Task $task);

    /**
     * Event called before a task is serialized.
     *
     * You can use this event to prepare data in the task to be serialized.
     *
     * @param Task $task
     */
    public function beforeTaskSerialization(Task $task);
}
