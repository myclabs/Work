<?php

namespace UnitTest\MyCLabs\Work\Dispatcher;

use MyCLabs\Work\Dispatcher\RabbitMQWorkDispatcher;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\Worker\RabbitMQWorker;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PHPUnit_Framework_TestCase;

/**
 * Test executing tasks through RabbitMQ
 */
class RabbitMQTest extends PHPUnit_Framework_TestCase
{
    const QUEUE_NAME = 'myclabs_work_test';

    /**
     * @var AMQPConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    public function setUp()
    {
        try {
            $this->connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
        } catch (AMQPRuntimeException $e) {
            // RabbitMQ not installed, mark test skipped
            $this->markTestSkipped('RabbitMQ is not installed or was not found');
            return;
        }
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare(self::QUEUE_NAME, false, false, false, false);
    }

    public function tearDown()
    {
        $this->channel->close();
        $this->connection->close();
    }

    public function testRunBackground()
    {
        $workDispatcher = new RabbitMQWorkDispatcher($this->channel, self::QUEUE_NAME);

        // Pile up a task to execute
        $task = new FakeTask();
        $workDispatcher->runBackground($task);

        // Run the worker to execute the task

        $worker = new RabbitMQWorker($this->channel, self::QUEUE_NAME);

        // Check that event methods are called
        $listener = $this->getMock('MyCLabs\Work\Worker\EventListener');
        $listener->expects($this->once())
            ->method('beforeTaskExecution');
        $listener->expects($this->once())
            ->method('onTaskSuccess');
        $worker->addEventListener($listener);

        // Fake task executor
        $taskExecutor = $this->getMockForAbstractClass('MyCLabs\Work\TaskExecutor\TaskExecutor');
        $taskExecutor->expects($this->once())
            ->method('execute')
            ->with($task);
        $worker->registerTaskExecutor(get_class($task), $taskExecutor);

        // Work
        $worker->work(1);
    }

    public function testRunBackgroundWithException()
    {
        $workDispatcher = new RabbitMQWorkDispatcher($this->channel, self::QUEUE_NAME);

        // Pile up a task to execute
        $task = new FakeTask();
        $workDispatcher->runBackground($task);

        // Run the worker to execute the task

        $worker = new RabbitMQWorker($this->channel, self::QUEUE_NAME);

        // Check that event methods are called
        $listener = $this->getMock('MyCLabs\Work\Worker\EventListener');
        $listener->expects($this->once())
            ->method('beforeTaskExecution');
        $listener->expects($this->once())
            ->method('onTaskException');
        $worker->addEventListener($listener);

        // Fake task executor
        $taskExecutor = $this->getMockForAbstractClass('MyCLabs\Work\TaskExecutor\TaskExecutor');
        $taskExecutor->expects($this->once())
            ->method('execute')
            ->with($task)
            ->will($this->throwException(new \Exception()));
        $worker->registerTaskExecutor(get_class($task), $taskExecutor);

        // Work
        $worker->work(1);
    }
}

class FakeTask implements Task
{
    public function __toString()
    {
        return '';
    }
}
