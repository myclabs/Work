<?php

namespace UnitTest\MyCLabs\Work\Task;

use MyCLabs\Work\Worker\SimpleWorker;
use PHPUnit_Framework_TestCase;

class SimpleWorkerTest extends PHPUnit_Framework_TestCase
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

        $worker = new SimpleWorker();
        $worker->registerTaskExecutor(get_class($task), $executor);

        $result = $worker->executeTask($task);

        // Check that the result is returned
        $this->assertSame('foo', $result);
    }
}
