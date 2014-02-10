<?php

namespace Test\MyCLabs\Work\UnitTest\Dispatcher;

use MyCLabs\Work\Adapter\InMemory\InMemoryWorkDispatcher;
use PHPUnit_Framework_TestCase;

class SimpleWorkDispatcherTest extends PHPUnit_Framework_TestCase
{
    public function testRunBackground()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');

        $worker = $this->getMock('MyCLabs\Work\Worker\SimpleWorker');
        // Check that the worker is called with the task as parameter
        $worker->expects($this->once())
            ->method('executeTask')
            ->with($task)
            ->will($this->returnValue('foo'));

        $dispatcher = new InMemoryWorkDispatcher($worker);

        $dispatcher->run($task);
    }

    public function testCallbackSuccess()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');

        $worker = $this->getMock('MyCLabs\Work\Worker\SimpleWorker');
        $worker->expects($this->once())
            ->method('executeTask')
            ->will($this->returnValue('foo'));

        $dispatcher = new InMemoryWorkDispatcher($worker);

        // Check that "completed" is called, but not "timeout"
        $mock = $this->getMock('stdClass', ['completed', 'timeout', 'errored']);
        $mock->expects($this->once())
            ->method('completed');
        $mock->expects($this->never())
            ->method('timeout');
        $mock->expects($this->never())
            ->method('errored');

        $dispatcher->run($task, 1, [$mock, 'completed'], [$mock, 'timeout'], [$mock, 'errored']);
    }

    public function testCallbackError()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');

        $worker = $this->getMock('MyCLabs\Work\Worker\SimpleWorker');
        $worker->expects($this->once())
            ->method('executeTask')
            ->will($this->throwException(new \Exception('foo')));

        $dispatcher = new InMemoryWorkDispatcher($worker);

        // Check that "errored" is called
        $mock = $this->getMock('stdClass', ['completed', 'timeout', 'errored']);
        $mock->expects($this->never())
            ->method('completed');
        $mock->expects($this->never())
            ->method('timeout');
        $mock->expects($this->once())
            ->method('errored')
            ->with($this->isInstanceOf('Exception'));

        $dispatcher->run($task, 1, [$mock, 'completed'], [$mock, 'timeout'], [$mock, 'errored']);
    }
}
