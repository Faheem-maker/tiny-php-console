<?php

namespace framework\console\commands;

use framework\Application;
use framework\console\Command;

class MakeMigrationCommand extends Command
{
    public string $signature = 'make:migration';

    public function execute(Application $app, array $args): void
    {
        if (empty($args)) {
            echo "Error: Migration name is required.\n";
            echo "Example: php tiny make:migration create_users_table\n";
            return;
        }

        $name = strtolower($args[0]);
        $timestamp = date('Y_m_d_His');
        
        // Generate class name from snake_case or similar
        $className = str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $name)));
        
        $fileName = $timestamp . '_' . $name . '.php';

        // Resolve path to migrations directory
        $targetDir = $app->path->resolve('@app/migrations');

        if (!is_dir($targetDir)) {
            if (mkdir($targetDir, 0755, true)) {
                echo "Created migrations directory: {$targetDir}\n";
            } else {
                echo "Error: Failed to create migrations directory.\n";
                return;
            }
        }

        $targetFile = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        if (file_exists($targetFile)) {
            echo "Error: Migration already exists at '{$targetFile}'.\n";
            return;
        }

        // Load template
        $templatePath = __DIR__ . '/../templates/migration.tpl';

        if (!file_exists($templatePath)) {
            echo "Error: Migration template not found at '{$templatePath}'.\n";
            return;
        }

        $template = file_get_contents($templatePath);
        $content = str_replace('{{className}}', $className, $template);

        if (file_put_contents($targetFile, $content)) {
            echo "Migration '{$fileName}' created successfully.\n";
        } else {
            echo "Error: Failed to create migration file.\n";
        }
    }
}
