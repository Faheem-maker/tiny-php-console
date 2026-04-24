<?php

namespace framework\console\commands;

use framework\Application;
use framework\console\Command;

class RollbackCommand extends Command
{
    public string $signature = 'migrate:rollback';

    public function execute(Application $app, array $args): void
    {
        if (empty($args)) {
            echo "Error: Migration ID or name is required for rollback.\n";
            echo "Usage: php tiny migrate:rollback <id|name>\n";
            return;
        }

        $id = $args[0];
        $db = $app->db;

        // Find migration by ID or name
        $query = $db->select('*')->from('migrations');
        if (is_numeric($id)) {
            $query->where('id', $id);
        } else {
            $query->where('migration_name', $id);
        }

        $migrationRow = $query->first();

        if (!$migrationRow) {
            echo "Error: Migration '{$id}' not found in the database.\n";
            return;
        }

        $file = $migrationRow['migration_name'];
        $migrationsDir = $app->path->resolve('@app/migrations');
        $filePath = $migrationsDir . DIRECTORY_SEPARATOR . $file;

        if (!file_exists($filePath)) {
            echo "Error: Migration file '{$file}' not found at '{$filePath}'.\n";
            return;
        }

        require_once $filePath;
        $className = $this->getClassNameFromFile($file);

        if (class_exists($className)) {
            $migration = new $className();
            if (method_exists($migration, 'down')) {
                echo "Rolling back migration: {$file}...\n";
                
                try {
                    $migration->down();

                    // Remove from migrations table
                    $db->delete('migrations')->where('id', $migrationRow['id'])->execute();

                    echo "Successfully rolled back: {$file}\n";
                } catch (\Exception $e) {
                    echo "Error during rollback: " . $e->getMessage() . "\n";
                }
            } else {
                echo "Error: Migration class '{$className}' does not have a 'down' method.\n";
            }
        } else {
            echo "Error: Migration class '{$className}' not found in '{$file}'.\n";
        }
    }

    /**
     * Extracts the class name from the migration filename.
     */
    private function getClassNameFromFile(string $file): string
    {
        $name = pathinfo($file, PATHINFO_FILENAME);
        $parts = explode('_', $name);
        
        // Skip the first 4 parts (Y_m_d_His)
        $classNameParts = array_slice($parts, 4);
        
        if (empty($classNameParts)) {
            $classNameParts = $parts;
        }

        $className = implode(' ', $classNameParts);
        $className = str_replace(['_', '-'], ' ', $className);
        $className = str_replace(' ', '', ucwords($className));

        return "app\\migrations\\" . $className;
    }
}
