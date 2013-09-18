<?php

namespace MyCLabs\Work\Worker;

/**
 * Execute tasks that have been queued.
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
interface Worker
{
    /**
     * Handle tasks that have been queued
     */
    public function work();
}
