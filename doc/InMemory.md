# Synchronous implementation

The `InMemory` adapter doesn't use any work queue. All the tasks are executed immediately and
synchronously in the same process.

This implementation is useful if you don't want to install a work queue on your development machine,
or in tests too.

It implements the same interface as the other adapters, so you can use it the same way:

```php
$workDispatcher = new InMemoryWorkDispatcher(new InMemoryWorker());

// Run a task
$task = new MyTask();
$workDispatcher->run($task);
```

There is (obviously) no need for a worker, since the worker is already running in the same
process.

This is basically the same as executing the task directly.

## Waiting for a task to finish

The `InMemory` adapter also implements the `SynchronousWorkDispatcher` interface, which
allows to run tasks and fetch its result:

```php
$result = $workDispatcher->runAndWait(new MyTask());
```
