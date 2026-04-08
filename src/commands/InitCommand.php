<?php

namespace framework\console\commands;

use framework\Application;
use framework\console\Command;

class InitCommand extends Command
{
    public string $signature = 'init';

    public function execute(Application $app, array $args): void
    {
        $cwd = getcwd();
        $source = $cwd . DIRECTORY_SEPARATOR . '.env.example';
        $destination = $cwd . DIRECTORY_SEPARATOR . '.env';

        if (!file_exists($source)) {
            echo "Error: .env.example file not found in current directory.\n";
            return;
        }

        if (file_exists($destination)) {
            echo "Warning: .env file already exists. Skipping initialization.\n";
            return;
        }

        if (copy($source, $destination)) {
            echo ".env file created successfully from .env.example.\n";
        } else {
            echo "Error: Failed to copy .env.example to .env.\n";
        }
    }
}
