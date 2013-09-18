<?php

namespace MyCLabs\Work\Dispatcher;

use MyCLabs\Work\Task\Task;
use MyCLabs\Work\TaskExecutor\TaskExecutor;

/**
 * Simple implementation not using any work queue: tasks are executed right away in the same process.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class SimpleWorkDispatcher implements WorkDispatcher
{
    /**
     * @var TaskExecutor[]
     */
    private $workers = [];

    /**
     * {@inheritdoc}
     */
    public function runBackground(Task $task)
    {
        $worker = $this->getExecutor($task);

        if (! $worker) {
            throw new \Exception("No executor was configured for task of type " . get_class($task));
        }

        $worker->execute($task);
    }

    /**
     * @param Task $task
     * @return TaskExecutor|null
     */
    private function getExecutor(Task $task)
    {
        $taskType = get_class($task);

        if (array_key_exists($taskType, $this->workers)) {
            return $this->workers[$taskType];
        }

        return null;
    }
}
