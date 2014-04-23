<?php

namespace Test\MyCLabs\Work\UnitTest\Adapter\InMemory;

use MyCLabs\Work\Adapter\InMemory\InMemoryWorkDispatcher;
use PHPUnit_Framework_TestCase;

/**
 * @covers \MyCLabs\Work\Adapter\InMemory\InMemoryWorkDispatcher
 */
class InMemoryWorkDispatcherTest extends PHPUnit_Framework_TestCase
{
    public function testRun()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');

        $worker = $this->getMock('MyCLabs\Work\Adapter\InMemory\InMemoryWorker');
        // Check that the worker is called with the task as parameter
        $worker->expects($this->once())
            ->method('executeTask')
            ->with($task);

        $dispatcher = new InMemoryWorkDispatcher($worker);

        $dispatcher->run($task);
    }

    public function testRunAndWaitCallbackSuccess()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');

        $worker = $this->getMock('MyCLabs\Work\Adapter\InMemory\InMemoryWorker');
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

        $dispatcher->runAndWait($task, 1, [$mock, 'completed'], [$mock, 'timeout'], [$mock, 'errored']);
    }

    public function testRunAndWaitCallbackError()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');

        $worker = $this->getMock('MyCLabs\Work\Adapter\InMemory\InMemoryWorker');
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

        $dispatcher->runAndWait($task, 1, [$mock, 'completed'], [$mock, 'timeout'], [$mock, 'errored']);
    }
}
