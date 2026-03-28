<?php

namespace framework\console;

use framework\Application;
use ReflectionClass;
use ReflectionProperty;

class CommandRouter
{
    private array $argv;
    private string $commandPath;

    public function __construct(array $argv)
    {
        $this->argv = $argv;
        $this->commandPath = __DIR__ . '/commands';
    }

    public function execute(Application $app): void
    {
        if (count($this->argv) < 2) {
            echo "Usage: php tiny <command> [args] [--options]\n";
            return;
        }

        // Separate options from positional arguments
        $options = [];
        $positional = [];
        
        // Skip argv[0] (the script name)
        for ($i = 1; $i < count($this->argv); $i++) {
            $arg = $this->argv[$i];
            if (str_starts_with($arg, '--')) {
                $options[] = $arg;
            } else {
                $positional[] = $arg;
            }
        }

        if (empty($positional)) {
            echo "No command specified.\n";
            return;
        }

        $commandSignature = array_shift($positional);
        $command = $this->findCommand($commandSignature);

        if (!$command) {
            echo "Command '{$commandSignature}' not found.\n";
            return;
        }

        $this->populateOptions($command, $options);
        $command->execute($app, $positional);
    }

    /**
     * Finds the command class matching the signature.
     */
    private function findCommand(string $signature): ?Command
    {
        if (!is_dir($this->commandPath)) {
            return null;
        }

        $files = scandir($this->commandPath);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $className = 'framework\\console\\commands\\' . str_replace('.php', '', $file);

            if (class_exists($className)) {
                $ref = new ReflectionClass($className);
                if ($ref->isSubclassOf(Command::class) && !$ref->isAbstract()) {
                    $instance = new $className();
                    if ($instance->signature === $signature) {
                        return $instance;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Populates class properties based on CLI options.
     */
    private function populateOptions(Command $command, array $options): void
    {
        $ref = new ReflectionClass($command);
        $properties = $ref->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($options as $arg) {
            $parts = explode('=', substr($arg, 2), 2);
            $key = $parts[0];
            
            // Note: For simplicity, we only support --key=value in this updated logic
            // or just --key (which sets to true)
            $value = count($parts) === 2 ? $parts[1] : true;

            foreach ($properties as $prop) {
                if ($prop->getName() === $key) {
                    $prop->setValue($command, $value);
                    break;
                }
            }
        }
    }
}
