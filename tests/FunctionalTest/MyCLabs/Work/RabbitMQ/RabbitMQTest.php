<?php

namespace UnitTest\MyCLabs\Work\Dispatcher;

use MyCLabs\Work\Dispatcher\RabbitMQWorkDispatcher;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PHPUnit_Framework_TestCase;

/**
 * Test executing tasks through RabbitMQ
 */
class RabbitMQTest extends PHPUnit_Framework_TestCase
{
    const QUEUE_NAME = 'myclabs_work_test';

    public function testRunBackground()
    {
        try {
            $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
        } catch (AMQPRuntimeException $e) {
            // RabbitMQ not installed, mark test skipped
            $this->markTestSkipped('RabbitMQ is not installed or was not found');
            return;
        }

        $channel = $connection->channel();

        $channel->queue_declare(self::QUEUE_NAME, false, false, false, false);

        $workDispatcher = new RabbitMQWorkDispatcher($channel, self::QUEUE_NAME);

        $channel->close();
        $connection->close();
    }
}
