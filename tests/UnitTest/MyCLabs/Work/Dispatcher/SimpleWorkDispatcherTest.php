<?php

namespace UnitTest\MyCLabs\Work\Dispatcher;

use MyCLabs\Work\Dispatcher\SimpleWorkDispatcher;
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

        $dispatcher = new SimpleWorkDispatcher($worker);

        $dispatcher->runBackground($task);
    }

    public function testCallbackSuccess()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');

        $worker = $this->getMock('MyCLabs\Work\Worker\SimpleWorker');
        $worker->expects($this->once())
            ->method('executeTask')
            ->will($this->returnValue('foo'));

        $dispatcher = new SimpleWorkDispatcher($worker);

        // Check that "completed" is called, but not "timeout"
        $mock = $this->getMock('stdClass', ['completed', 'timeout', 'errored']);
        $mock->expects($this->once())
            ->method('completed');
        $mock->expects($this->never())
            ->method('timeout');
        $mock->expects($this->never())
            ->method('errored');

        $dispatcher->runBackground($task, 1, [$mock, 'completed'], [$mock, 'timeout'], [$mock, 'errored']);
    }

    public function testCallbackError()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');

        $worker = $this->getMock('MyCLabs\Work\Worker\SimpleWorker');
        $worker->expects($this->once())
            ->method('executeTask')
            ->will($this->throwException(new \Exception('foo')));

        $dispatcher = new SimpleWorkDispatcher($worker);

        // Check that "errored" is called
        $mock = $this->getMock('stdClass', ['completed', 'timeout', 'errored']);
        $mock->expects($this->never())
            ->method('completed');
        $mock->expects($this->never())
            ->method('timeout');
        $mock->expects($this->once())
            ->method('errored');

        $dispatcher->runBackground($task, 1, [$mock, 'completed'], [$mock, 'timeout'], [$mock, 'errored']);
    }
}
