<?php

namespace FunctionalTest\MyCLabs\Work\RabbitMQ;

use MyCLabs\Work\Dispatcher\RabbitMQWorkDispatcher;
use MyCLabs\Work\TaskExecutor\TaskExecutor;
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
    const QUEUE_PREFIX = 'myclabs_work_test';

    /**
     * @var AMQPConnection
     */
    private $connection;

    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var string
     */
    private $queue;

    public function setUp()
    {
        try {
            $this->connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
        } catch (AMQPRuntimeException $e) {
            // RabbitMQ not installed, mark test skipped
            $this->markTestSkipped('RabbitMQ is not installed or was not found');
            return;
        }
        $this->queue = self::QUEUE_PREFIX . '_' . rand();
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare($this->queue, false, false, false, false);
    }

    public function tearDown()
    {
        if ($this->channel) {
            $this->channel->queue_delete($this->queue);
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }

    public function testSimpleRunBackground()
    {
        $workDispatcher = new RabbitMQWorkDispatcher($this->channel, $this->queue);

        // Pile up a task to execute
        $task = new FakeTask();
        $workDispatcher->runBackground($task);

        // Run the worker to execute the task
        $worker = new RabbitMQWorker($this->channel, $this->queue);

        // Check that event methods are called
        $listener = $this->getMock('MyCLabs\Work\EventListener');
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
        $workDispatcher = new RabbitMQWorkDispatcher($this->channel, $this->queue);

        // Pile up a task to execute
        $task = new FakeTask();
        $workDispatcher->runBackground($task);

        // Run the worker to execute the task
        $worker = new RabbitMQWorker($this->channel, $this->queue);

        // Check that event methods are called
        $listener = $this->getMock('MyCLabs\Work\EventListener');
        $listener->expects($this->once())
            ->method('beforeTaskExecution');
        $listener->expects($this->once())
            ->method('onTaskError');
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

    /**
     * Test that if we wait for a task and it times out, the callback is called
     */
    public function testRunBackgroundWithTimeout()
    {
        $workDispatcher = new RabbitMQWorkDispatcher($this->channel, $this->queue);

        // Check that "timeout" is called, but not "completed" or "errored"
        $mock = $this->getMock('stdClass', ['completed', 'timeout', 'errored']);
        $mock->expects($this->never())
            ->method('completed');
        $mock->expects($this->once())
            ->method('timeout');
        $mock->expects($this->never())
            ->method('errored');

        // Pile up a task to execute and let it timeout
        $workDispatcher->runBackground(
            new FakeTask(),
            0.01,
            [$mock, 'completed'],
            [$mock, 'timeout'],
            [$mock, 'errored']
        );
    }

    /**
     * Test the Dispatcher with waiting for the job to complete
     */
    public function testRunBackgroundWait()
    {
        $workDispatcher = new RabbitMQWorkDispatcher($this->channel, $this->queue);

        // Run the worker as background task
        $file = __DIR__ . '/worker.php';
        $log = __DIR__ . '/worker.log';
        $triggerError = 0;
        exec("php $file {$this->queue} $triggerError > $log 2> $log &");

        // Check that "completed" is called, but not "timeout" or "errored"
        $mock = $this->getMock('stdClass', ['completed', 'timeout', 'errored']);
        $mock->expects($this->once())
            ->method('completed');
        $mock->expects($this->never())
            ->method('timeout');
        $mock->expects($this->never())
            ->method('errored');

        $workDispatcher->runBackground(new FakeTask(), 1, [$mock, 'completed'], [$mock, 'timeout'], [$mock, 'errored']);

        // Check that the log is empty (no error)
        $this->assertStringEqualsFile($log, 'ok');
    }

    /**
     * Test the Dispatcher with waiting for the job to complete, and the job errors
     */
    public function testRunBackgroundWaitError()
    {
        $workDispatcher = new RabbitMQWorkDispatcher($this->channel, $this->queue);

        // Run the worker as background task
        $file = __DIR__ . '/worker.php';
        $log = __DIR__ . '/worker.log';
        $triggerError = 1;
        exec("php $file {$this->queue} $triggerError > $log 2> $log &");

        // Check that "completed" is called, but not "timeout" or "errored"
        $mock = $this->getMock('stdClass', ['completed', 'timeout', 'errored']);
        $mock->expects($this->never())
            ->method('completed');
        $mock->expects($this->never())
            ->method('timeout');
        $mock->expects($this->once())
            ->method('errored')
            ->with($this->isInstanceOf('Exception'));

        $workDispatcher->runBackground(new FakeTask(), 1, [$mock, 'completed'], [$mock, 'timeout'], [$mock, 'errored']);

        // Check that the log is empty (no error)
        $this->assertStringEqualsFile($log, '');
    }

    /**
     * Test the Worker with waiting for the job to complete
     */
    public function testWorkWithWait()
    {
        $worker = new RabbitMQWorker($this->channel, $this->queue);
        /** @var TaskExecutor $taskExecutor */
        $taskExecutor = $this->getMockForAbstractClass('MyCLabs\Work\TaskExecutor\TaskExecutor');
        $worker->registerTaskExecutor('FunctionalTest\MyCLabs\Work\RabbitMQ\FakeTask', $taskExecutor);

        // Run the task dispatcher as background task (it will emit 1 task and wait for it)
        $file = __DIR__ . '/dispatch-task.php';
        $log = __DIR__ . '/dispatch-task.log';
        $wait = 1;
        exec("php $file {$this->queue} $wait > $log 2> $log &");

        // Check that the events are called
        $listener = $this->getMock('MyCLabs\Work\EventListener');
        $listener->expects($this->once())
            ->method('afterTaskUnserialization');
        $listener->expects($this->once())
            ->method('beforeTaskExecution');
        $listener->expects($this->once())
            ->method('beforeTaskFinished');
        $listener->expects($this->once())
            ->method('onTaskSuccess')
            // Check that $dispatcherNotified = true
            ->with($this->anything(), true);
        $listener->expects($this->never())
            ->method('onTaskException');
        $worker->addEventListener($listener);

        // Execute 1 task
        $worker->work(1);

        // Check that the log is empty (no error)
        $this->assertStringEqualsFile($log, '');
    }

    /**
     * Test the Worker with waiting for the job to complete, except the Dispatcher timeout and stop waiting.
     * The worker executes a task in 500ms, the dispatcher wait for 1ms.
     */
    public function testWorkWithWaitDispatcherTimeout()
    {
        $worker = new RabbitMQWorker($this->channel, $this->queue);
        $taskExecutor = $this->getMockForAbstractClass('MyCLabs\Work\TaskExecutor\TaskExecutor');
        $taskExecutor->expects($this->once())
            ->method('execute')
            ->will($this->returnCallback(function () {
                // The task executes in 500ms
                usleep(500000);
            }));
        $worker->registerTaskExecutor('FunctionalTest\MyCLabs\Work\RabbitMQ\FakeTask', $taskExecutor);

        // Run the task dispatcher as background task (it will emit 1 task and wait for it)
        $file = __DIR__ . '/dispatch-task.php';
        $log = __DIR__ . '/dispatch-task.log';
        // The worker waits for 1ms
        $wait = 0.001;
        exec("php $file {$this->queue} $wait > $log 2> $log &");

        // Check that the events are called
        $listener = $this->getMock('MyCLabs\Work\EventListener');
        $listener->expects($this->once())
            ->method('afterTaskUnserialization');
        $listener->expects($this->once())
            ->method('beforeTaskExecution');
        $listener->expects($this->once())
            ->method('beforeTaskFinished');
        $listener->expects($this->once())
            ->method('onTaskSuccess')
            // Check that $dispatcherNotified = false
            ->with($this->anything(), false);
        $listener->expects($this->never())
            ->method('onTaskException');
        $worker->addEventListener($listener);

        $worker->work(1);
    }

    /**
     * Test when the worker start after the dispatcher has emitted 1 task and timeouted.
     * The problem met was that the exchange didn't exist anymore.
     */
    public function testWorkerStartAfterDispatcherTimeout()
    {
        $worker = new RabbitMQWorker($this->channel, $this->queue);
        $taskExecutor = $this->getMockForAbstractClass('MyCLabs\Work\TaskExecutor\TaskExecutor');
        $worker->registerTaskExecutor('FunctionalTest\MyCLabs\Work\RabbitMQ\FakeTask', $taskExecutor);

        // Run the task dispatcher and wait for it to timeout and finish
        $file = __DIR__ . '/dispatch-task.php';
        // The worker waits for 1ms
        $wait = 0.001;
        $return = exec("php $file {$this->queue} $wait");
        // Check that the log is empty (no error)
        $this->assertEquals('', $return);

        $listener = $this->getMock('MyCLabs\Work\EventListener');
        $listener->expects($this->once())
            ->method('onTaskSuccess')
            // Check that $dispatcherNotified = false
            ->with($this->anything(), false);
        $worker->addEventListener($listener);

        $worker->work(1);
    }

    /**
     * Test a scenario where tasks are piling up in RabbitMQ
     */
    public function testTaskQueuing()
    {
        $workDispatcher = new RabbitMQWorkDispatcher($this->channel, $this->queue);

        // Queue 2 tasks
        $workDispatcher->runBackground(new FakeTask(), 0.1);
        $workDispatcher->runBackground(new FakeTask(), 0.1);

        $file = __DIR__ . '/worker.php';

        // Process first task
        $status = shell_exec("php $file {$this->queue} 0 2>&1");
        $this->assertSame("ok", trim($status));

        // Process second task
        $status = shell_exec("php $file {$this->queue} 0 2>&1");
        $this->assertSame("ok", trim($status));
    }
}
