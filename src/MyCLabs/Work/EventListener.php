<?php

namespace MyCLabs\Work;

use Exception;
use MyCLabs\Work\Task\Task;

/**
 * Interface for implementing a listener for events.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
abstract class EventListener
{
    /**
     * Event called before a task is dispatched to be run by a worker.
     *
     * You can use this event to prepare the task to be sent.
     *
     * @param Task $task
     */
    public function beforeTaskDispatched(Task $task)
    {
    }

    /**
     * Event called before a task is serialized.
     *
     * You can use this event to prepare data in the task to be serialized.
     *
     * @param Task $task
     */
    public function beforeTaskSerialization(Task $task)
    {
    }

    /**
     * Event called after a task is unserialized.
     *
     * You can use this event to restore the state of the task.
     *
     * @param Task $task
     */
    public function afterTaskUnserialization(Task $task)
    {
    }

    /**
     * Event called before a task is executed.
     *
     * If an exception is thrown in this method, then the task will be considered as errored
     * and the onTaskException event will be called.
     *
     * @param Task $task
     */
    public function beforeTaskExecution(Task $task)
    {
    }

    /**
     * Event called after a task is executed successfully.
     *
     * If an exception is thrown in this method, then the task will be considered as errored
     * and the onTaskException event will be called.
     *
     * @param Task $task
     */
    public function onTaskSuccess(Task $task)
    {
    }

    /**
     * Event called when a task was executed but threw an exception.
     *
     * @param Task      $task
     * @param Exception $e
     */
    public function onTaskException(Task $task, Exception $e)
    {
    }
}
