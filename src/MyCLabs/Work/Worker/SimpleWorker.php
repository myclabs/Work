<?php

namespace MyCLabs\Work\Worker;

use MyCLabs\Work\Task\Task;

/**
 * Simple implementation not using any work queue: tasks are executed right away in the same process.
 *
 * @see SimpleWorkDispatcher
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class SimpleWorker extends Worker
{
    /**
     * {@inheritdoc}
     */
    public function work()
    {
        // Nothing to do, there is no async worker in the "Simple" implementation
    }

    /**
     * Synchronously execute a task.
     *
     * @param Task $task
     * @return mixed Result
     */
    public function executeTask(Task $task)
    {
        $executor = $this->getExecutor($task);

        return $executor->execute($task);
    }
}
