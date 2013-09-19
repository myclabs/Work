# Listening for Work events

If you want to execute special behavior before or after a task is executed (for example for logging,
db connections, …), you can implement the EventListener abstract class:

```php
class MyEventListener extends MyCLabs\Work\Worker\EventListener
{
    /**
     * Event called before a task is executed.
     *
     * If an exception is thrown in this method, then the task will be considered as errored
     * and the onTaskException event will be called.
     *
     * @param Task $task
     */
    public function beforeTaskExecution(Task $task)
    {
        // ...
    }

    /**
     * Event called after a task is executed successfully.
     *
     * If an exception is thrown in this method, then the task will be considered as errored
     * and the onTaskException event will be called.
     *
     * @param Task $task
     */
    public function onTaskSuccess(Task $task)
    {
        // ...
    }

    /**
     * Event called when a task was executed but threw an exception.
     *
     * @param Task      $task
     * @param Exception $e
     */
    public function onTaskException(Task $task, Exception $e)
    {
        // ...
    }
}
```
