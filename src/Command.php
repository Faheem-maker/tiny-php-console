<?php

namespace framework\console;

use framework\Application;

abstract class Command
{
    public string $signature;

    /**
     * Execute the command.
     */
    abstract public function execute(Application $app, array $args): void;
}
