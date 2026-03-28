<?php

namespace framework\console\commands;

use framework\Application;
use framework\console\Command;

class MakeModelCommand extends Command
{
    public string $signature = 'make:model';

    public function execute(Application $app, array $args): void
    {
        if (empty($args)) {
            echo "Error: Model name is required as a positional argument.\n";
            echo "Example: php tiny make:model TestModel\n";
            return;
        }

        $modelName = ucfirst($args[0]);

        // Resolve path to controllers directory
        $targetDir = $app->path->resolve('@app/http/models');

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetFile = $targetDir . DIRECTORY_SEPARATOR . $modelName . '.php';

        if (file_exists($targetFile)) {
            echo "Error: Model already exists at '{$targetFile}'.\n";
            return;
        }

        // Load template
        $templatePath = __DIR__ . '/../templates/model.tpl';
        if (!file_exists($templatePath)) {
            echo "Error: Model template not found at '{$templatePath}'.\n";
            return;
        }

        $template = file_get_contents($templatePath);
        $content = str_replace('{{className}}', $modelName, $template);

        if (file_put_contents($targetFile, $content)) {
            echo "Model '{$modelName}' created successfully at '{$targetFile}'.\n";
        } else {
            echo "Error: Failed to create model file.\n";
        }
    }
}
