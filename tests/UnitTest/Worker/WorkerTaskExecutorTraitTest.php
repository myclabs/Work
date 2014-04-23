<?php

namespace Test\MyCLabs\Work\UnitTest\Worker;

use MyCLabs\Work\Dispatcher\WorkDispatcherEventTrait;
use MyCLabs\Work\Worker\WorkerEventTrait;
use MyCLabs\Work\Worker\WorkerTaskExecutorTrait;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\TaskExecutor\TaskExecutor;
use PHPUnit_Framework_TestCase;

/**
 * @covers \MyCLabs\Work\Worker\WorkerTaskExecutorTrait
 */
class WorkerTaskExecutorTraitTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterExecutor()
    {
        /** @var Task $task */
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');
        /** @var TaskExecutor $executor */
        $executor = $this->getMockForAbstractClass('MyCLabs\Work\TaskExecutor\TaskExecutor');

        // Register an executor
        $fixture = new FixtureClass;
        $fixture->registerTaskExecutor(get_class($task), $executor);

        // Check that it is returned
        $executor = $fixture->getTraitExecutor($task);
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

        $fixture = new FixtureClass;
        $fixture->getTraitExecutor($task);
    }
}

class FixtureClass
{
    use WorkerTaskExecutorTrait;

    public function getTraitExecutor(Task $task)
    {
        return $this->getExecutor($task);
    }
}
