<?php

namespace UnitTest\MyCLabs\Work\Dispatcher;

use MyCLabs\Work\Dispatcher\WorkDispatcher;
use MyCLabs\Work\Task\Task;
use PHPUnit_Framework_TestCase;

class WorkDispatcherTest extends PHPUnit_Framework_TestCase
{
    public function testEventListener()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');
        $listener = $this->getMock('MyCLabs\Work\EventListener');

        // Check that event methods are called
        $listener->expects($this->once())
            ->method('beforeTaskSerialization');

        $worker = new FakeWorkDispatcher();

        $worker->addEventListener($listener);
        $worker->runBackground($task);
    }
}

class FakeWorkDispatcher extends WorkDispatcher
{
    public function runBackground(Task $task)
    {
        $this->triggerEvent(self::EVENT_BEFORE_TASK_SERIALIZATION, [$task]);
    }
}
