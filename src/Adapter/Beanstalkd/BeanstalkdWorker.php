<?php

namespace MyCLabs\Work\Adapter\Beanstalkd;

use Exception;
use MyCLabs\Work\Worker\WorkerEventTrait;
use MyCLabs\Work\Worker\WorkerTaskExecutorTrait;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\Worker\Worker;
use Pheanstalk_Job;
use Pheanstalk_PheanstalkInterface;

/**
 * Beanstalkd implementation.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class BeanstalkdWorker implements Worker
{
    use WorkerEventTrait;
    use WorkerTaskExecutorTrait;

    /**
     * @var Pheanstalk_PheanstalkInterface
     */
    private $connection;

    /**
     * @var string
     */
    private $tube;

    /**
     * @param Pheanstalk_PheanstalkInterface $connection
     * @param string                         $tube
     */
    public function __construct(Pheanstalk_PheanstalkInterface $connection, $tube)
    {
        $this->connection = $connection;
        $this->tube = $tube;
    }

    /**
     * {@inheritdoc}
     */
    public function work($count = null)
    {
        while (is_null($count) || ($count > 0)) {
            /** @var Pheanstalk_Job $job */
            $job = $this->connection->reserveFromTube($this->tube);

            $task = unserialize($job->getData());

            if ($task instanceof Task) {
                $this->execute($task);
            } else {
                // This is not a task, we bury the job
                $this->connection->bury($job);
            }

            if (! is_null($count)) {
                $count--;
            }
        }
    }

    private function execute(Task $task)
    {
        try {
            $this->triggerEvent(self::EVENT_AFTER_TASK_UNSERIALIZATION, [$task]);
            $this->triggerEvent(self::EVENT_BEFORE_TASK_EXECUTION, [$task]);

            // Execute the task
            $this->getExecutor($task)->execute($task);

            $this->triggerEvent(self::EVENT_BEFORE_TASK_FINISHED, [$task]);
            $this->triggerEvent(self::EVENT_ON_TASK_SUCCESS, [$task, false]);
        } catch (Exception $e) {
            $this->triggerEvent(self::EVENT_ON_TASK_ERROR, [$task, $e, false]);
        }
    }
}
