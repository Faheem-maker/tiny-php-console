<?php

namespace framework\console\commands;

use framework\Application;
use framework\console\Command;

class MakeControllerCommand extends Command
{
    public string $signature = 'make:controller';

    public function execute(Application $app, array $args): void
    {
        if (empty($args)) {
            echo "Error: Controller name is required as a positional argument.\n";
            echo "Example: php tiny make:controller TestController\n";
            return;
        }

        $controllerName = ucfirst($args[0]);

        // Ensure controller name is properly formatted
        if (!str_ends_with($controllerName, 'Controller')) {
            $controllerName .= 'Controller';
        }

        // Resolve path to controllers directory
        $targetDir = $app->path->resolve('@app/http/controllers');

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetFile = $targetDir . DIRECTORY_SEPARATOR . $controllerName . '.php';

        if (file_exists($targetFile)) {
            echo "Error: Controller already exists at '{$targetFile}'.\n";
            return;
        }

        // Load template
        $templatePath = __DIR__ . '/../templates/controller.tpl';
        if (!file_exists($templatePath)) {
            echo "Error: Controller template not found at '{$templatePath}'.\n";
            return;
        }

        $template = file_get_contents($templatePath);
        $content = str_replace('{{className}}', $controllerName, $template);

        if (file_put_contents($targetFile, $content)) {
            echo "Controller '{$controllerName}' created successfully at '{$targetFile}'.\n";
        } else {
            echo "Error: Failed to create controller file.\n";
        }
    }
}
