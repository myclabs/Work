<?php

use MyCLabs\Work\Task\Task;
use MyCLabs\Work\TaskExecutor\TaskExecutor;
use MyCLabs\Work\Adapter\RabbitMQ\Worker\RabbitMQWorker;
use PhpAmqpLib\Connection\AMQPConnection;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

require_once __DIR__ . '/../../../../../vendor/autoload.php';

$queue = $argv[1];
$error = $argv[2];

class FakeTaskExecutor implements TaskExecutor
{
    public function execute(Task $task)
    {
        global $error;
        if ($error) {
            throw new \Exception('foo');
        }
        echo "ok";
    }
}


$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$worker = new RabbitMQWorker($channel, $queue);
$worker->registerTaskExecutor('FunctionalTest\MyCLabs\Work\RabbitMQ\FakeTask', new FakeTaskExecutor());

// Execute 1 task
$worker->work(1);

$channel->close();
$connection->close();
