<?php

namespace MyCLabs\Work\Task;

/**
 * Task.
 *
 * Must be serializable.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface Task
{
    /**
     * Return a string representation of the task, useful for logging and debugging.
     *
     * @return string
     */
    public function __toString();
}
