# Work

[![Build Status](https://travis-ci.org/myclabs/Work.png?branch=master)](https://travis-ci.org/myclabs/Work) [![Coverage Status](https://coveralls.io/repos/myclabs/Work/badge.png?branch=master)](https://coveralls.io/r/myclabs/Work?branch=master) [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/myclabs/Work/badges/quality-score.png?s=1b6757c137dd42e383dc459edf474bcfdbc935be)](https://scrutinizer-ci.com/g/myclabs/Work/)

`Work` is a work queue library letting you run distributed tasks using a generic abstraction.

It's intent is to be compatible with major work queue solutions (RabbitMQ, Gearman, …) while offering a high level abstraction.


Current implementations:

- InMemory: synchronous implementation, task are executed directly (useful for tests or dev environments)
- [RabbitMQ](http://www.rabbitmq.com/)

Feel free to contribute and submit other implementations (Gearman, Beanstalkd, …).

Extended guides:

- [Use RabbitMQ with Work](doc/RabbitMQ.md)
- [Listening to events](doc/Events.md)

## How it works

In you code (HTTP request for example), you can run a task in background:

```php
$workDispatcher = new RabbitMQWorkDispatcher(/* parameters */);
$workDispatcher->run(new MyTask());
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

## Execute a task and wait for its result

The `run($task)` method runs a task in background.

If you want to wait for the result of that task, you have to use a WorkDispatcher that implements the
`\MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher` interface. For example, the RabbitMQ adapter implements this interface.

That interface offers the `runAndWait` method:

```php
    /**
     * Run a task in background.
     *
     * You can use $wait to wait a given time for the task to complete.
     * If the task hasn't finished during this time, $timedout will be called and this method will return.
     * If the task has finished, $completed will be called.
     *
     * @param Task     $task
     * @param int      $wait      Number of seconds to wait for the task to complete. If 0, doesn't wait.
     * @param callable $completed Called (if $wait > 0) when the task has completed.
     * @param callable $timedout  Called (if $wait > 0) if we hit the timeout while waiting.
     * @param callable $errored   Called (if $wait > 0) if the task errors. Takes 1 parameter which is the exception.
     *
     * @return void No results
     */
    public function runAndWait(
        Task $task,
        $wait = 0,
        callable $completed = null,
        callable $timedout = null,
        callable $errored = null
    );
```

## Read more

Read more in [the docs](doc/).

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
