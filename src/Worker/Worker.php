<?php

namespace MyCLabs\Work\Worker;

use MyCLabs\Work\TaskExecutor\TaskExecutor;
use MyCLabs\Work\Worker\Event\WorkerEventListener;

/**
 * Execute tasks that have been queued.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface Worker
{
    /**
     * Event: after a task is unserialized.
     */
    const EVENT_AFTER_TASK_UNSERIALIZATION = 'afterTaskUnserialization';

    /**
     * Event: before a task is executed.
     */
    const EVENT_BEFORE_TASK_EXECUTION = 'beforeTaskExecution';

    /**
     * Event: after a task is executed (without error).
     */
    const EVENT_BEFORE_TASK_FINISHED = 'beforeTaskFinished';

    /**
     * Event: when a task is executed successfully.
     */
    const EVENT_ON_TASK_SUCCESS = 'onTaskSuccess';

    /**
     * Event: when a task was executed but threw an exception.
     */
    const EVENT_ON_TASK_ERROR = 'onTaskError';

    /**
     * Handle tasks that have been queued
     *
     * @param int $count Number of task to execute. If null, then loop infinitely
     */
    public function work($count = null);

    /**
     * Registers an executor that will handle task of a certain type.
     *
     * @param string       $taskType Class name of the task
     * @param TaskExecutor $executor
     */
    public function registerTaskExecutor($taskType, TaskExecutor $executor);

    /**
     * @param WorkerEventListener $listener
     */
    public function registerEventListener(WorkerEventListener $listener);
}
