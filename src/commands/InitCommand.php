<?php

namespace framework\console\commands;

use framework\Application;
use framework\console\Command;

class InitCommand extends Command
{
    public string $signature = 'init';

    public function execute(Application $app, array $args): void
    {
        $rootPath = dirname(realpath($_SERVER['SCRIPT_FILENAME']));
        $source = $rootPath . DIRECTORY_SEPARATOR . '.env.example';
        $destination = $rootPath . DIRECTORY_SEPARATOR . '.env';

        if (!file_exists($source)) {
            echo "Error: .env.example file not found in '{$rootPath}'.\n";
            return;
        }

        if (file_exists($destination)) {
            echo "Warning: .env file already exists in '{$rootPath}'. Skipping initialization.\n";
            return;
        }

        if (copy($source, $destination)) {
            echo ".env file created successfully in '{$rootPath}'.\n";
        } else {
            echo "Error: Failed to copy .env.example to .env in '{$rootPath}'.\n";
        }
    }
}
