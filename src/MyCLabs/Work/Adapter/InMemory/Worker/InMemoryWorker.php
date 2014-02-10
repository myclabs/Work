<?php

namespace MyCLabs\Work\Adapter\InMemory\Worker;

use Exception;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\Worker\Worker;

/**
 * Simple implementation not using any work queue: tasks are executed right away in the same process.
 *
 * @see SimpleWorkDispatcher
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InMemoryWorker extends Worker
{
    /**
     * {@inheritdoc}
     */
    public function work($count = null)
    {
        // Nothing to do, there is no async worker in the "Simple" implementation
    }

    /**
     * Synchronously execute a task.
     *
     * @param Task $task
     * @throws \Exception
     * @return mixed Result
     */
    public function executeTask(Task $task)
    {
        try {
            // Event: before
            $this->triggerEvent(self::EVENT_BEFORE_TASK_EXECUTION, [$task]);

            // Execute the task
            $result = $this->getExecutor($task)->execute($task);

            // Event: after
            $this->triggerEvent(self::EVENT_BEFORE_TASK_FINISHED, [$task]);
        } catch (Exception $e) {
            // Event: error
            $this->triggerEvent(self::EVENT_ON_TASK_ERROR, [$task, $e, true]);

            // Rethrow the exception
            throw $e;
        }

        $this->triggerEvent(self::EVENT_ON_TASK_SUCCESS, [$task, true]);

        return $result;
    }
}
