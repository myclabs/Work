<?php

use MyCLabs\Work\Adapter\Beanstalkd\BeanstalkdWorker;
use MyCLabs\Work\Task\Task;
use MyCLabs\Work\TaskExecutor\TaskExecutor;

ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

require_once __DIR__ . '/../../../vendor/autoload.php';

$tube = $argv[1];
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

$worker = new BeanstalkdWorker(new Pheanstalk_Pheanstalk('127.0.0.1'), $tube);
$worker->registerTaskExecutor('Test\MyCLabs\Work\FunctionalTest\FakeTask', new FakeTaskExecutor());

// Execute 1 task
$worker->work(1);
