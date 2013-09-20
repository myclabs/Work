<?php

namespace MyCLabs\Work\Worker;

use Exception;
use MyCLabs\Work\Task\Task;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

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
        $callback = function(AMQPMessage $message) {
            $this->taskHandler($message);
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
     * Handles a task.
     *
     * @param mixed $message
     */
    private function taskHandler(AMQPMessage $message)
    {
        /** @var AMQPChannel $channel */
        $channel = $message->delivery_info['channel'];

        /** @var Task $task */
        $task = unserialize($message->body);

        try {
            $this->triggerEvent(self::EVENT_AFTER_TASK_UNSERIALIZATION, [$task]);

            $this->triggerEvent(self::EVENT_BEFORE_TASK_EXECUTION, [$task]);

            // Execute the task
            $this->getExecutor($task)->execute($task);

            $this->triggerEvent(self::EVENT_ON_TASK_SUCCESS, [$task]);

            // Send ACK signaling the task execution is over
            $channel->basic_ack($message->delivery_info['delivery_tag']);

            // If the emitter wants a reply, we reply
            if ($message->has('reply_to')) {
                $this->signalSuccess($message->get('reply_to'));
            }

        } catch (Exception $e) {
            $this->triggerEvent(self::EVENT_ON_TASK_EXCEPTION, [$task, $e]);

            // Signal the task execution has failed
            $channel->basic_reject($message->delivery_info['delivery_tag'], false);
        }
    }

    /**
     * Signal to the emitter of the task that we finished.
     *
     * @param string $queue
     */
    private function signalSuccess($queue)
    {
        // We put in the queue that we finished
        $this->channel->basic_publish(new AMQPMessage('finished'), '', $queue);

        // Read the first message coming out of the queue
        $message = $this->waitForMessage($queue, 0.5);

        if (! $message) {
            // Shouldn't happen -> error while delivering messages?
            return;
        }

        // If the first message of the queue is a "timeout" message from the emitter
        if ($message->body == 'timeout') {
            // Delete the temporary queue
            $this->channel->queue_delete($queue);
        }

        // If the first message of the queue is our "finished" message, we can die in peace
        if ($message->body == 'finished') {
        }
    }

    /**
     * Read a queue until there's a message or until a timeout.
     *
     * @param string $queue
     * @param int    $timeout Time to wait in seconds
     * @return AMQPMessage|null
     */
    private function waitForMessage($queue, $timeout)
    {
        $timeStart = microtime(true);

        do {
            // Get message and auto-ack
            $response = $this->channel->basic_get($queue);
            if ($response) {
                return $response;
            }

            // Sleep 300 ms
            usleep(300000);
            $timeSpent = microtime(true) - $timeStart;
        } while ($timeSpent < $timeout);

        return null;
    }
}
