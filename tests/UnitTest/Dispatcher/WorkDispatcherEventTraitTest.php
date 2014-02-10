<?php

namespace Test\MyCLabs\Work\UnitTest\Dispatcher;

use MyCLabs\Work\Dispatcher\WorkDispatcherEventTrait;
use PHPUnit_Framework_TestCase;

class WorkDispatcherEventTraitTest extends PHPUnit_Framework_TestCase
{
    public function testEventListener()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');
        $listener = $this->getMockForAbstractClass('MyCLabs\Work\Dispatcher\Event\DispatcherEventListener');

        // Check that event methods are called
        $listener->expects($this->once())
            ->method('beforeTaskSerialization')
            ->with($task);

        $worker = new FakeWorkDispatcher();

        $worker->registerEventListener($listener);
        $worker->trigger('beforeTaskSerialization', [$task]);
    }
}

class FakeWorkDispatcher
{
    use WorkDispatcherEventTrait;

    public function trigger($event, array $params)
    {
        $this->triggerEvent($event, $params);
    }
}
