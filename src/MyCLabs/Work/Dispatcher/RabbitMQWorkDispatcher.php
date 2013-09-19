<?php

namespace MyCLabs\Work\Dispatcher;

use MyCLabs\Work\Task\Task;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * RabbitMQ implementation.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class RabbitMQWorkDispatcher extends WorkDispatcher
{
    /**
     * @var AMQPChannel
     */
    private $channel;

    /**
     * @var string
     */
    private $queue;

    /**
     * @param AMQPChannel $channel
     * @param string      $queue
     */
    public function __construct(AMQPChannel $channel, $queue)
    {
        $this->channel = $channel;
        $this->queue = $queue;
    }

    /**
     * {@inheritdoc}
     */
    public function runBackground(Task $task)
    {
        // Event: before serialization
        $this->triggerEvent(self::EVENT_BEFORE_TASK_SERIALIZATION, [$task]);

        $message = new AMQPMessage(
            serialize($task),
            [
                'delivery_mode' => 2, // make message persistent
            ]
        );

        $this->channel->basic_publish($message, '', $this->queue);
    }
}
