<?php

namespace MyCLabs\Work\Worker;

use Exception;
use MyCLabs\Work\Task\Task;
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
    public function work($count = null)
    {
        $callback = function($message) {
            $this->workHandler($message);
        };

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume($this->queue, '', false, false, false, false, $callback);

        // Loop infinitely (or up to $count) to execute tasks
        while (count($this->channel->callbacks) && (is_null($count) || ($count > 0))) {
            $this->channel->wait();

            if (! is_null($count)) {
                $count--;
            }
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

        /** @var Task $task */
        $task = unserialize($message->body);

        try {
            // Event: before
            $this->triggerEvent(self::EVENT_BEFORE_TASK_EXECUTION, [$task]);

            // Execute the task
            $this->getExecutor($task)->execute($task);

            // Event: after
            $this->triggerEvent(self::EVENT_ON_TASK_SUCCESS, [$task]);

            // Send ACK signaling the task execution is over
            $channel->basic_ack($message->delivery_info['delivery_tag']);

        } catch (Exception $e) {
            // Event: error
            $this->triggerEvent(self::EVENT_ON_TASK_EXCEPTION, [$task, $e]);

            // Signal the task execution has failed
            $channel->basic_reject($message->delivery_info['delivery_tag'], false);
        }
    }
}
