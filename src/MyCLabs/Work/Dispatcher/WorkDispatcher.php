<?php

namespace MyCLabs\Work\Dispatcher;

use MyCLabs\Work\Task\Task;

/**
 * Dispatch tasks.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface WorkDispatcher
{
    /**
     * Run a task in background
     *
     * @param Task $task
     * @return void No results
     */
    public function runBackground(Task $task);
}
