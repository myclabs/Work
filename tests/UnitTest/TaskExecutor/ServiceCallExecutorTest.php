<?php

namespace Test\MyCLabs\Work\UnitTest\TaskExecutor;

use MyCLabs\Work\Task\ServiceCall;
use MyCLabs\Work\TaskExecutor\ServiceCallExecutor;
use PHPUnit_Framework_TestCase;

/**
 * @covers \MyCLabs\Work\TaskExecutor\ServiceCallExecutor
 */
class ServiceCallExecutorTest extends PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        // Call method "bar" with 3 parameters
        $task = new ServiceCall('foo', 'bar', [1, 2, 3]);

        $service = $this->getMockForAbstractClass('Test\MyCLabs\Work\UnitTest\TaskExecutor\Service');
        // Check that the service's method is called with the parameters
        $service->expects($this->once())
            ->method('bar')
            ->with(1, 2, 3)
            ->will($this->returnValue('Hello World'));

        $serviceLocator = new FakeServiceLocator($service);

        $executor = new ServiceCallExecutor($serviceLocator);
        $result = $executor->execute($task);

        // Check that the result is returned
        $this->assertSame('Hello World', $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid task type provided
     */
    public function testInvalidTaskType()
    {
        $task = $this->getMockForAbstractClass('MyCLabs\Work\Task\Task');
        $executor = new ServiceCallExecutor(new \stdClass());
        $executor->execute($task);
    }
}

/**
 * Fixture class
 */
class FakeServiceLocator
{
    private $service;

    public function __construct($service)
    {
        $this->service = $service;
    }
    public function get($name)
    {
        return $this->service;
    }
}

/**
 * Fixture interface
 */
interface Service
{
    public function bar($param1, $param2, $param3);
}
