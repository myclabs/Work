<?php

namespace UnitTest\MyCLabs\Work\Worker;

use MyCLabs\Work\Worker\RabbitMQWorker;
use PHPUnit_Framework_TestCase;

class RabbitMQWorkerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider range
     */
    public function testLimitNumberOfTask($times)
    {
        $channel = $this->getMock(
            'PhpAmqpLib\Channel\AMQPChannel',
            ['basic_qos', 'basic_consume', 'wait'],
            [],
            '',
            false
        );
        $channel->callbacks = 1;

        // Check that it will loop only X times
        $channel->expects($this->exactly($times))
            ->method('wait');

        $worker = new RabbitMQWorker($channel, '');
        $worker->work($times);
    }

    public function range()
    {
        return array(
            array(0),
            array(1),
            array(2),
            array(3)
        );
    }
}
