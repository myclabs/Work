<?php

namespace MyCLabs\Work\Dispatcher;

use MyCLabs\Work\Task\Task;
use MyCLabs\Work\Worker\SimpleWorker;

/**
 * Simple implementation not using any work queue: tasks are executed right away in the same process.
 *
 * @see SimpleWorker
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class SimpleWorkDispatcher extends WorkDispatcher
{
    /**
     * @var SimpleWorker
     */
    private $worker;

    /**
     * The SimpleWorkDispatcher executes task synchronously, so it needs to know the
     * worker to be able to call it directly.
     *
     * @param SimpleWorker $simpleWorker
     */
    public function __construct(SimpleWorker $simpleWorker)
    {
        $this->worker = $simpleWorker;
    }

    /**
     * {@inheritdoc}
     * This implementation is synchronous, so it will wait for the task to complete.
     */
    public function runBackground(
        Task $task,
        $wait = 0,
        callable $completed = null,
        callable $timedout = null,
        callable $errored = null
    ) {
        $this->triggerEvent(self::EVENT_BEFORE_TASK_DISPATCHED, [$task]);

        try {
            $this->worker->executeTask($task);
        } catch (\Exception $e) {
            if ($errored) {
                call_user_func($errored);
            }
            return;
        }

        if ($completed) {
            call_user_func($completed);
        }
    }
}
