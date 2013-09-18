<?php

namespace MyCLabs\Work\Dispatcher;

use MyCLabs\Work\Task\Task;

/**
 * Simple implementation not using any work queue: tasks are executed right away in the same process.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class SimpleWorkDispatcher implements WorkDispatcher
{
    /**
     * Workers indexés par le nom de la tâche qu'ils traitent
     * @var Worker[]
     */
    private $workers = [];

    /**
     * {@inheritdoc}
     */
    public function runBackground(Task $task)
    {
        $worker = $this->getWorker($task);

        $worker->execute($task);
    }

    /**
     * Retourne le worker enregistré pour une tâche donnée
     * @param Task $task
     * @return Worker|null
     */
    private function getWorker(Task $task)
    {
        $taskType = get_class($task);

        if (array_key_exists($taskType, $this->workers)) {
            return $this->workers[$taskType];
        }

        return null;
    }
}
