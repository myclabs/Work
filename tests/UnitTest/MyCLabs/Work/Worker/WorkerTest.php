<?php

namespace UnitTest\MyCLabs\Work\Worker;

use MyCLabs\Work\Task\Task;
use MyCLabs\Work\TaskExecutor\TaskExecutor;
use MyCLabs\Work\Worker\Worker;
use PHPUnit_Framework_TestCase;

class WorkerTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterExecutor()
    {
        /** @var Task $task */
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');
        /** @var TaskExecutor $executor */
        $executor = $this->getMockForAbstractClass('MyCLabs\Work\TaskExecutor\TaskExecutor');

        // Register an executor
        /** @var Worker $worker */
        $worker = $this->getMockForAbstractClass('MyCLabs\Work\Worker\Worker');
        $worker->registerTaskExecutor(get_class($task), $executor);

        // Check that it is returned
        $executor = $worker->getExecutor($task);
        $this->assertSame($executor, $executor);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage No executor was configured for task of type
     */
    public function testNonExistentExecutor()
    {
        /** @var Task $task */
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');

        /** @var Worker $worker */
        $worker = $this->getMockForAbstractClass('MyCLabs\Work\Worker\Worker');

        $worker->getExecutor($task);
    }
}
