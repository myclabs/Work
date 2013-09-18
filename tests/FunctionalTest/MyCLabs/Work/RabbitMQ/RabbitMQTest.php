<?php

namespace UnitTest\MyCLabs\Work\Dispatcher;

use MyCLabs\Work\Dispatcher\RabbitMQWorkDispatcher;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\Worker\RabbitMQWorker;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PHPUnit_Framework_TestCase;

/**
 * Test executing tasks through RabbitMQ
 */
class RabbitMQTest extends PHPUnit_Framework_TestCase
{
    const QUEUE_NAME = 'myclabs_work_test';

    public function testRunBackground()
    {
        try {
            $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
        } catch (AMQPRuntimeException $e) {
            // RabbitMQ not installed, mark test skipped
            $this->markTestSkipped('RabbitMQ is not installed or was not found');
            return;
        }

        $channel = $connection->channel();

        $channel->queue_declare(self::QUEUE_NAME, false, false, false, false);

        $workDispatcher = new RabbitMQWorkDispatcher($channel, self::QUEUE_NAME);

        // Pile up a task to execute
        $task = new FakeTask();
        $workDispatcher->runBackground($task);

        // Run the worker to execute the task
        $taskExecutor = $this->getMockForAbstractClass('MyCLabs\Work\TaskExecutor\TaskExecutor');
        $taskExecutor->expects($this->once())
            ->method('execute')
            ->with($task);
        $worker = new RabbitMQWorker($channel, self::QUEUE_NAME);
        $worker->registerTaskExecutor(get_class($task), $taskExecutor);
        $worker->work(1);

        $channel->close();
        $connection->close();
    }
}

class FakeTask implements Task
{
}
