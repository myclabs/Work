<?php

namespace MyCLabs\Work\Adapter\Beanstalkd;

use MyCLabs\Work\Dispatcher\WorkDispatcher;
use MyCLabs\Work\Dispatcher\WorkDispatcherEventTrait;
use MyCLabs\Work\Task\Task;
use Pheanstalk_PheanstalkInterface;

/**
 * Beanstalkd implementation.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class BeanstalkdWorkDispatcher implements WorkDispatcher
{
    use WorkDispatcherEventTrait;

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
    public function run(Task $task)
    {
        // Event: before dispatching the task
        $this->triggerEvent(self::EVENT_BEFORE_TASK_DISPATCHED, [$task]);

        // Event: before serialization
        $this->triggerEvent(self::EVENT_BEFORE_TASK_SERIALIZATION, [$task]);

        $this->connection->putInTube($this->tube, serialize($task));
    }
}
