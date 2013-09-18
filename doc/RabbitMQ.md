# Using RabbitMQ

This guide is inspired from the official RabbitMQ docs, you should read them first.

On your client side (MVC application for example):

```php
// Connect to the RabbitMQ server
$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
// Create a queue
$channel->queue_declare('some_queue', false, false, false, false);

$workDispatcher = new RabbitMQWorkDispatcher($channel, 'some_queue');

// Run a task in background
$task = new MyTask();
$workDispatcher->runBackground($task);
```

On the worker side (this script is meant to be run continuously as a deamon):

```php
// Connect to the RabbitMQ server
$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
// Create a queue
$channel->queue_declare('some_queue', false, false, false, false);

$worker = new RabbitMQWorker($channel, 'some_queue');
$worker->registerTaskExecutor('MyTask', new MyTaskExecutor());

// Execute tasks
$worker->work();
```
