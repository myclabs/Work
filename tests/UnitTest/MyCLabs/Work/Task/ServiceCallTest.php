<?php

namespace UnitTest\MyCLabs\Work\Task;

use MyCLabs\Work\Task\ServiceCall;
use PHPUnit_Framework_TestCase;

class ServiceCallTest extends PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $task = new ServiceCall('foo', 'bar', [1, 2, 3]);

        $this->assertSame('foo', $task->getServiceName());
        $this->assertSame('bar', $task->getMethodName());
        $this->assertSame([1, 2, 3], $task->getParameters());
    }
}
