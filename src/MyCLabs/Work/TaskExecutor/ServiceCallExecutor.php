<?php

namespace MyCLabs\Work\TaskExecutor;

use MyCLabs\Work\Task\ServiceCall;
use MyCLabs\Work\Task\Task;
use Psr\Log\LoggerInterface;

/**
 * Calls the method of a service.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ServiceCallExecutor implements TaskExecutor
{
    private $serviceLocator;

    /**
     * @param mixed $serviceLocator Container/Service locator, must implement get($serviceName) method
     */
    public function __construct($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritdoc}
     * @param ServiceCall $task
     */
    public function execute(Task $task)
    {
        if (! $task instanceof ServiceCall) {
            throw new \Exception("Invalid task type provided");
        }

        $serviceName = $task->getServiceName();
        $methodName = $task->getMethodName();
        $parameters = $task->getParameters();

        // Get the service from the service locator
        $service = $this->serviceLocator->get($serviceName);

        $return = call_user_func_array([$service, $methodName], $parameters);

        return $return;
    }
}
