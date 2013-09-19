# Work

`Work` is a work queue library letting you run distributed tasks using a generic abstraction.

It's intent is to be compatible with major work queue solutions (RabbitMQ, Gearman, …) while offering a high level abstraction.


Current implementations:

- Simple: synchronous implementation, task are executed directly (useful for tests or dev environments)
- [RabbitMQ](http://www.rabbitmq.com/)

Feel free to contribute and submit other implementations (Gearman, Beanstalkd, …).

Extended guides:

- [Use RabbitMQ with Work](doc/RabbitMQ.md)
- [Listening to events](doc/Events.md)

## How it works

In you code (HTTP request for example), you can run a task in background:

```php
$workDispatcher = new RabbitMQWorkDispatcher(/* parameters */);
$workDispatcher->runBackground(new MyTask());
```

Separately, you set up a worker to run continuously on the command line (like a deamon):

```shell
$ php my-worker.php
```

This worker simply calls:

```php
// my-worker.php
$worker = new RabbitMQWorker(/* parameters */);
// Will loop continuously and execute tasks
$worker->work();
```

### Defining tasks

Define a task:

```php
class BigComputation implements MyCLabs\Work\Task\Task
{
    public $parameter1;
}
```

And define the code that executes the task:

```php
class BigComputationExecutor implements MyCLabs\Work\TaskExecutor\TaskExecutor
{
    public function execute(Task $task)
    {
        if (! $task instanceof BigComputation) {
            throw new \Exception("Invalid task type provided");
        }
        // Perform the action (here we just multiply the parameter by 2)
        return $task->parameter1 * 2;
    }
}
```

## Contributing

You can run the tests with PHPUnit:

```shell
$ composer install
$ phpunit
```

Some functional tests need external programs like RabbitMQ. For practical reasons, you can boot a VM
very quickly using Vagrant and the included configuration. You can then run the tests in the VM:

```shell
$ vagrant up
$ vagrant ssh
$ cd /vagrant
$ composer install
$ phpunit
```
