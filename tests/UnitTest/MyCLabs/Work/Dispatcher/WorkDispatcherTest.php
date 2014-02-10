<?php

namespace UnitTest\MyCLabs\Work\Dispatcher;

use MyCLabs\Work\Dispatcher\WorkDispatcherEventTrait;
use PHPUnit_Framework_TestCase;

class WorkDispatcherTest extends PHPUnit_Framework_TestCase
{
    public function testEventListener()
    {
        $listener = $this->getMock('MyCLabs\Work\EventListener');

        // Check that event methods are called
        $listener->expects($this->once())
            ->method('beforeTaskSerialization')
            ->with('foo', 'bar');

        $worker = new FakeWorkDispatcher();

        $worker->registerEventListener($listener);
        $worker->trigger('beforeTaskSerialization', ['foo', 'bar']);
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
