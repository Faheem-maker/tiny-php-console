<?php

namespace framework\console;

use framework\Application;

class ConsoleApplication extends Application
{
    private array $argv;

    /**
     * Private constructor to enforce singleton
     */
    private function __construct(array $argv)
    {
        $this->argv = $argv;
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(array $argv): self
    {
        if (static::$instance === null) {
            static::$instance = new static($argv);
        }

        return static::$instance;
    }

    public function run()
    {
        $router = new CommandRouter($this->argv);
        $router->execute($this);
    }
}
