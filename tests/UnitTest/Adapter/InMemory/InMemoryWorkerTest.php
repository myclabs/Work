<?php

namespace Test\MyCLabs\Work\UnitTest\Adapter\InMemory;

use MyCLabs\Work\Adapter\InMemory\InMemoryWorker;
use PHPUnit_Framework_TestCase;

/**
 * @covers \MyCLabs\Work\Adapter\InMemory\InMemoryWorker
 */
class InMemoryWorkerTest extends PHPUnit_Framework_TestCase
{
    public function testExecuteTask()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');

        // Check that the executor is called with the task as parameter
        $executor = $this->getMockForAbstractClass('MyCLabs\Work\TaskExecutor\TaskExecutor');
        $executor->expects($this->once())
            ->method('execute')
            ->with($task)
            ->will($this->returnValue('foo'));

        $worker = new InMemoryWorker();
        $worker->registerTaskExecutor(get_class($task), $executor);

        $result = $worker->executeTask($task);

        // Check that the result is returned
        $this->assertSame('foo', $result);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage foo
     */
    public function testExceptionIsNotSuppressed()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');

        $exception = new \RuntimeException('foo');

        // Check that the executor is called with the task as parameter
        $executor = $this->getMockForAbstractClass('MyCLabs\Work\TaskExecutor\TaskExecutor');
        $executor->expects($this->once())
            ->method('execute')
            ->with($task)
            ->will($this->throwException($exception));

        $worker = new InMemoryWorker();
        $worker->registerTaskExecutor(get_class($task), $executor);
        $worker->executeTask($task);
    }
}
