# Using Beanstalkd

The Beanstalkd adapter uses the [Pheanstalk](https://github.com/pda/pheanstalk) library.

You need to require the library in composer:

```json
{
    "require": {
        "myclabs/work": "*",
        "pda/pheanstalk": "~2.0"
    }
}
```

On your client side (MVC application for example):

```php
// Connect to the Beanstalkd server
$connection = new Pheanstalk_Pheanstalk('127.0.0.1');
// Use the following tube
$tube = 'my_tube';

$workDispatcher = new BeanstalkdWorkDispatcher($connection, $tube);

// Run a task in background
$task = new MyTask();
$workDispatcher->run($task);
```

On the worker side (this script is meant to be run continuously as a deamon):

```php
// Connect to the RabbitMQ server
$connection = new Pheanstalk_Pheanstalk('127.0.0.1');
// Use the following tube
$tube = 'my_tube';

$worker = new BeanstalkdWorker($connection, $tube);
$worker->registerTaskExecutor('MyTask', new MyTaskExecutor());

// Execute tasks
$worker->work();
```

## Waiting for a task to finish

The Beanstalkd adapter doesn't support the features provided by the `SynchronousWorkDispatcher` interface.

In other words, you can't run a task and wait for it to finish, because Beanstalkd doesn't
offer that feature.

If you really need that feature, use another adapter that supports it (for example the RabbitMQ adapter).
