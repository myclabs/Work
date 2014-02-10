<?php

namespace MyCLabs\Work\Adapter\InMemory;

use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use MyCLabs\Work\Dispatcher\WorkDispatcherEventTrait;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\Adapter\InMemory\InMemoryWorker;

/**
 * Simple implementation not using any work queue: tasks are executed right away in the same process.
 *
 * @see SimpleWorker
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class InMemoryWorkDispatcher implements WorkDispatcher, SynchronousWorkDispatcher
{
    use WorkDispatcherEventTrait;

    /**
     * @var \MyCLabs\Work\Adapter\InMemory\InMemoryWorker
     */
    private $worker;

    /**
     * The InMemoryWorkDispatcher executes task synchronously, so it needs to know the
     * worker to be able to call it directly.
     *
     * @param InMemoryWorker $simpleWorker
     */
    public function __construct(InMemoryWorker $simpleWorker)
    {
        $this->worker = $simpleWorker;
    }

    /**
     * {@inheritdoc}
     * This implementation is synchronous, so it will wait for the task to complete.
     */
    public function run(Task $task)
    {
        $this->triggerEvent(self::EVENT_BEFORE_TASK_DISPATCHED, [$task]);

        try {
            $this->worker->executeTask($task);
        } catch (\Exception $e) {
            // To keep the behavior consistent with any work queue, all exceptions are silenced/ignored
            // just like it would be if the task was running in background in a separate process
            return;
        }
    }

    /**
     * {@inheritdoc}
     * This implementation is synchronous, so it will wait for the task to complete and ignore timeouts.
     */
    public function runAndWait(
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
                call_user_func($errored, $e);
            }
            return;
        }

        if ($completed) {
            call_user_func($completed);
        }
    }
}
