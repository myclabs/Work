<?php

namespace MyCLabs\Work\Worker;

use MyCLabs\Work\Task\Task;
use MyCLabs\Work\TaskExecutor\TaskExecutor;

/**
 * Provides event methods for a work dispatcher.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
trait WorkerTaskExecutorTrait
{
    /**
     * Executors indexed by task name.
     * @var TaskExecutor[]
     */
    private $executors = [];

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
    protected function getExecutor(Task $task)
    {
        $taskType = get_class($task);

        if (array_key_exists($taskType, $this->executors)) {
            return $this->executors[$taskType];
        }

        throw new \Exception("No executor was configured for task of type " . get_class($task));
    }
}
