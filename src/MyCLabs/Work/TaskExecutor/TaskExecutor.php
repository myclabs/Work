<?php

namespace MyCLabs\Work\TaskExecutor;

use MyCLabs\Work\Task\Task;

/**
 * Execute a task.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface TaskExecutor
{
    /**
     * Execute a task.
     *
     * @param Task $task
     *
     * @return mixed Result
     */
    public function execute(Task $task);
}
