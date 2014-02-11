<?php

namespace Test\MyCLabs\Work\UnitTest\Worker;

use MyCLabs\Work\Dispatcher\WorkDispatcherEventTrait;
use MyCLabs\Work\Worker\WorkerEventTrait;
use MyCLabs\Work\Worker\WorkerTaskExecutorTrait;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\Worker\Worker;
use PHPUnit_Framework_TestCase;

/**
 * @covers \MyCLabs\Work\Worker\WorkerEventTrait
 */
class WorkerEventTraitTest extends PHPUnit_Framework_TestCase
{
    public function testEventListener()
    {
        $listener = $this->getMock('MyCLabs\Work\Worker\Event\WorkerEventListener');

        // Check that event methods are called
        $listener->expects($this->once())
            ->method('afterTaskUnserialization');
        $listener->expects($this->once())
            ->method('beforeTaskExecution');
        $listener->expects($this->once())
            ->method('beforeTaskFinished');
        $listener->expects($this->once())
            ->method('onTaskSuccess');
        $listener->expects($this->once())
            ->method('onTaskError');

        $fixture = new FixturesClass();

        $fixture->registerEventListener($listener);
        $fixture->trigger();
    }
}

class FixturesClass
{
    use \MyCLabs\Work\Worker\WorkerEventTrait;

    public function trigger()
    {
        $this->triggerEvent(Worker::EVENT_AFTER_TASK_UNSERIALIZATION, [new FakeTask()]);
        $this->triggerEvent(Worker::EVENT_BEFORE_TASK_EXECUTION, [new FakeTask()]);
        $this->triggerEvent(Worker::EVENT_BEFORE_TASK_FINISHED, [new FakeTask()]);
        $this->triggerEvent(Worker::EVENT_ON_TASK_ERROR, [new FakeTask(), new \Exception(), true]);
        $this->triggerEvent(Worker::EVENT_ON_TASK_SUCCESS, [new FakeTask(), true]);
    }
}

class FakeTask implements Task
{
}
