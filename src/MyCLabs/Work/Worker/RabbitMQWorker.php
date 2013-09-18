<?php

namespace MyCLabs\Work\Worker;

use PhpAmqpLib\Channel\AMQPChannel;

/**
 * RabbitMQ implementation.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class RabbitMQWorker extends Worker
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
    public function work()
    {
        $callback = function($message) {
            $this->workHandler($message);
        };

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($this->queue, '', false, false, false, false, $callback);

        // Loop infinitely to execute tasks
        while(count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    /**
     * Handles a message
     *
     * @param mixed $message
     */
    private function workHandler($message)
    {
        /** @var AMQPChannel $channel */
        $channel = $message->delivery_info['channel'];

        $task = unserialize($message->body);

        // Execute the task
        $this->getExecutor($task)->execute($task);

        // Send ACK signaling the task execution is over
        $channel->basic_ack($message->delivery_info['delivery_tag']);
    }
}
