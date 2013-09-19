<?php

namespace MyCLabs\Work\Worker;

use Exception;
use MyCLabs\Work\Task\Task;

/**
 * Base class for implementing a listener for Worker's events.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
abstract class EventListener
{
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
