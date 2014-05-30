<?php

namespace Test\MyCLabs\Work\UnitTest\Adapter\Beanstalkd;

use MyCLabs\Work\Adapter\Beanstalkd\BeanstalkdWorker;
use PHPUnit_Framework_TestCase;

/**
 * @covers \MyCLabs\Work\Adapter\Beanstalkd\BeanstalkdWorker
 */
class BeanstalkdWorkerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider range
     */
    public function testLimitNumberOfTask($times)
    {
        $pheanstalk = $this->getMockForAbstractClass('Pheanstalk_PheanstalkInterface');
        $job = $this->getMock('Pheanstalk_Job', [], [], '', false);
        $job->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(null));

        // Check that it will loop only X times
        $pheanstalk->expects($this->exactly($times))
            ->method('reserveFromTube')
            ->will($this->returnValue($job));

        $worker = new BeanstalkdWorker($pheanstalk, '');
        $worker->work($times);
    }

    public function range()
    {
        return [
            [0],
            [1],
            [2],
            [3]
        ];
    }
}
