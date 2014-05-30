<?php

namespace Test\MyCLabs\Work\FunctionalTest\Beanstalkd;

use MyCLabs\Work\Adapter\Beanstalkd\BeanstalkdWorkDispatcher;
use MyCLabs\Work\Adapter\Beanstalkd\BeanstalkdWorker;
use Pheanstalk_Pheanstalk;
use PHPUnit_Framework_TestCase;
use Test\MyCLabs\Work\FunctionalTest\FakeTask;

/**
 * Test executing tasks through Beanstalkd
 */
class BeanstalkdTest extends PHPUnit_Framework_TestCase
{
    const QUEUE_PREFIX = 'myclabs_work_test';

    /**
     * @var Pheanstalk_Pheanstalk
     */
    private $connection;

    /**
     * @var string
     */
    private $tube;

    public function setUp()
    {
        $this->connection = new Pheanstalk_Pheanstalk('127.0.0.1');
        if (! $this->connection->getConnection()->isServiceListening()) {
            // Beanstalkd not installed, mark test skipped
            $this->markTestSkipped('Beanstalkd is not installed or was not found');
            return;
        }
        $this->tube = self::QUEUE_PREFIX . '_' . rand();
    }

    public function testSimpleRun()
    {
        $dispatcher = new BeanstalkdWorkDispatcher($this->connection, $this->tube);

        // Pile up a task to execute
        $task = new FakeTask();
        $dispatcher->run($task);

        // Run the worker to execute the task
        $worker = new BeanstalkdWorker($this->connection, $this->tube);

        // Check that event methods are called
        $listener = $this->getMock('MyCLabs\Work\Worker\Event\WorkerEventListener');
        $listener->expects($this->once())
            ->method('beforeTaskExecution');
        $listener->expects($this->once())
            ->method('onTaskSuccess');
        $worker->registerEventListener($listener);

        // Fake task executor
        $taskExecutor = $this->getMockForAbstractClass('MyCLabs\Work\TaskExecutor\TaskExecutor');
        $taskExecutor->expects($this->once())
            ->method('execute')
            ->with($task);
        $worker->registerTaskExecutor(get_class($task), $taskExecutor);

        // Work
        $worker->work(1);
    }

    public function testRunWithException()
    {
        $workDispatcher = new BeanstalkdWorkDispatcher($this->connection, $this->tube);

        // Pile up a task to execute
        $task = new FakeTask();
        $workDispatcher->run($task);

        // Run the worker to execute the task
        $worker = new BeanstalkdWorker($this->connection, $this->tube);

        // Check that event methods are called
        $listener = $this->getMock('MyCLabs\Work\Worker\Event\WorkerEventListener');
        $listener->expects($this->once())
            ->method('beforeTaskExecution');
        $listener->expects($this->once())
            ->method('onTaskError');
        $worker->registerEventListener($listener);

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
     * Test a scenario where tasks are piling up in Beanstalkd
     */
    public function testTaskQueuing()
    {
        $workDispatcher = new BeanstalkdWorkDispatcher($this->connection, $this->tube);

        // Queue 2 tasks
        $workDispatcher->run(new FakeTask(), 0.1);
        $workDispatcher->run(new FakeTask(), 0.1);

        $file = __DIR__ . '/worker.php';

        // Process first task
        $status = shell_exec("php $file {$this->tube} 0 2>&1");
        $this->assertSame("ok", trim($status));

        // Process second task
        $status = shell_exec("php $file {$this->tube} 0 2>&1");
        $this->assertSame("ok", trim($status));
    }
}
