# Change log

## 0.2.0

Added support for receiving the exception in the `error` callback:

```php
$error = function (Exception $e) {
    echo "There was an error while completing the operation: " . $e->getMessage();
}

$workDispatcher->runBackground($task, 5, null, null, $error);
```

However the exception will not be the original exception that made the worker error. The reason for that
is that it is complex to serialize an exception and transmit it between the worker and the web request.

It becomes really useful though when using the `SimpleWorkDispatcher`, as you will get the original exception here.
It can help debugging a lot in a development environment.

## 0.1.0

First release.
