<?php

namespace Test\MyCLabs\Work\UnitTest\Dispatcher;

use MyCLabs\Work\Dispatcher\WorkDispatcherEventTrait;
use PHPUnit_Framework_TestCase;

/**
 * @covers \MyCLabs\Work\Dispatcher\WorkDispatcherEventTrait
 */
class WorkDispatcherEventTraitTest extends PHPUnit_Framework_TestCase
{
    public function testTriggerEvent()
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

    /**
     * Check that there are no errors
     */
    public function testTriggerEventWithNoListener()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');

        $worker = new FakeWorkDispatcher();

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
