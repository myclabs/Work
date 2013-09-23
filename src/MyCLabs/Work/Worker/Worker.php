<?php

namespace MyCLabs\Work\Worker;

use MyCLabs\Work\EventListener;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\TaskExecutor\TaskExecutor;

/**
 * Execute tasks that have been queued.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
abstract class Worker
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
    const EVENT_ON_TASK_EXCEPTION = 'onTaskException';

    /**
     * Executors indexed by task name.
     * @var TaskExecutor[]
     */
    private $executors = [];

    /**
     * @var EventListener[]
     */
    private $listeners = [];

    /**
     * Handle tasks that have been queued
     *
     * @param int $count Number of task to execute. If null, then loop infinitely
     */
    public abstract function work($count = null);

    /**
     * Registers an executor that will handle task of a certain type.
     *
     * @param string       $taskType Class name of the task
     * @param TaskExecutor $executor
     */
    public function registerTaskExecutor($taskType, TaskExecutor $executor)
    {
        $this->executors[$taskType] = $executor;
    }

    /**
     * Returns the executor that handles the given task.
     *
     * @param Task $task
     *
     * @throws \Exception No executor was configured for given task
     * @return TaskExecutor|null
     */
    public function getExecutor(Task $task)
    {
        $taskType = get_class($task);

        if (array_key_exists($taskType, $this->executors)) {
            return $this->executors[$taskType];
        }

        throw new \Exception("No executor was configured for task of type " . get_class($task));
    }

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
