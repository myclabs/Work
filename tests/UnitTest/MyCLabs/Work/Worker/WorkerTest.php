<?php

namespace UnitTest\MyCLabs\Work\Worker;

use MyCLabs\Work\Task\Task;
use MyCLabs\Work\TaskExecutor\TaskExecutor;
use MyCLabs\Work\Worker\Worker;
use PHPUnit_Framework_TestCase;

class WorkerTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterExecutor()
    {
        /** @var Task $task */
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');
        /** @var TaskExecutor $executor */
        $executor = $this->getMockForAbstractClass('MyCLabs\Work\TaskExecutor\TaskExecutor');

        // Register an executor
        /** @var Worker $worker */
        $worker = $this->getMockForAbstractClass('MyCLabs\Work\Worker\Worker');
        $worker->registerTaskExecutor(get_class($task), $executor);

        // Check that it is returned
        $executor = $worker->getExecutor($task);
        $this->assertSame($executor, $executor);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No executor was configured for task of type
     */
    public function testNonExistentExecutor()
    {
        /** @var Task $task */
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');

        /** @var Worker $worker */
        $worker = $this->getMockForAbstractClass('MyCLabs\Work\Worker\Worker');

        $worker->getExecutor($task);
    }

    public function testEventListener()
    {
        $listener = $this->getMock('MyCLabs\Work\Worker\EventListener');

        // Check that event methods are called
        $listener->expects($this->once())
            ->method('beforeTaskExecution');
        $listener->expects($this->once())
            ->method('onTaskSuccess');
        $listener->expects($this->once())
            ->method('onTaskException');

        $worker = new FakeWorker();

        $worker->addEventListener($listener);
        $worker->work();
    }
}

class FakeWorker extends Worker
{
    public function work($count = null)
    {
        $this->triggerEvent(self::EVENT_BEFORE_TASK_EXECUTION, [new FakeTask()]);
        $this->triggerEvent(self::EVENT_ON_TASK_SUCCESS, [new FakeTask()]);
        $this->triggerEvent(self::EVENT_ON_TASK_EXCEPTION, [new FakeTask(), new \Exception()]);
    }
}

class FakeTask implements Task
{
}
