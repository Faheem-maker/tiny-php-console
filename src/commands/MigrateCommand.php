<?php

namespace framework\console\commands;

use framework\Application;
use framework\console\Command;

class MigrateCommand extends Command
{
    public string $signature = 'migrate';

    public function execute(Application $app, array $args): void
    {
        $db = $app->db;

        // 1. Ensure migrations table exists
        if (!$db->isTable('migrations')) {
            echo "Creating migrations table...\n";
            $tableCmd = $db->createTable('migrations');
            $tableCmd->id();
            $tableCmd->string('migration_name', 255)->unique();
            $tableCmd->date('applied_at')->nullable();
            $tableCmd->execute();
            echo "Migrations table created.\n";
        }

        // 2. Resolve migration files
        $migrationsDir = $app->path->resolve('@app/migrations');
        if (!is_dir($migrationsDir)) {
            echo "No migrations found.\n";
            return;
        }

        $files = scandir($migrationsDir);
        $migrationFiles = [];
        foreach ($files as $file) {
            if (str_ends_with($file, '.php')) {
                $migrationFiles[] = $file;
            }
        }
        sort($migrationFiles);

        // 3. Get applied migrations
        $appliedMigrations = [];
        $rows = $db->select('migration_name')->from('migrations')->all();
        foreach ($rows as $row) {
            $appliedMigrations[] = $row['migration_name'];
        }

        // 4. Run pending migrations
        $count = 0;
        foreach ($migrationFiles as $file) {
            if (!in_array($file, $appliedMigrations)) {
                echo "Applying migration: {$file}...\n";
                
                require_once $migrationsDir . DIRECTORY_SEPARATOR . $file;
                
                // Extract class name from filename: YYYY_MM_DD_HHMMSS_ClassName.php
                // Or just the filename part minus extension
                $className = $this->getClassNameFromFile($file);
                
                if (class_exists($className)) {
                    $migration = new $className();
                    if (method_exists($migration, 'up')) {
                        $migration->up();
                        
                        // Record migration
                        $db->insert('migrations', [
                            'migration_name' => $file,
                            'applied_at' => date('Y-m-d H:i:s')
                        ]);
                        
                        echo "Applied: {$file}\n";
                        $count++;
                    } else {
                        echo "Error: Migration class '{$className}' does not have an 'up' method.\n";
                    }
                } else {
                    echo "Error: Migration class '{$className}' not found in '{$file}'.\n";
                }
            }
        }

        if ($count === 0) {
            echo "Nothing to migrate.\n";
        } else {
            echo "Successfully applied {$count} migration(s).\n";
        }
    }

    /**
     * Extracts the class name from the migration filename.
     * Expects format: timestamp_name.php
     * Example: 2026_04_15_192019_create_users_table.php -> CreateUsersTable
     */
    private function getClassNameFromFile(string $file): string
    {
        $name = pathinfo($file, PATHINFO_FILENAME);
        
        // Remove the timestamp prefix (e.g., 2026_04_15_123456_)
        // We look for the first part that is not numeric/underscore timestamp pattern
        $parts = explode('_', $name);
        
        // Typical Laravel-style timestamp is 4_2_2_6 = 14 chars + 1 underscore
        // But let's be flexible and just skip the first 4 parts (Y_m_d_His)
        $classNameParts = array_slice($parts, 4);
        
        if (empty($classNameParts)) {
            // Fallback if naming convention differs
            $classNameParts = $parts;
        }

        $className = implode(' ', $classNameParts);
        $className = str_replace(['_', '-'], ' ', $className);
        $className = str_replace(' ', '', ucwords($className));

        // Note: The migration file should have "namespace app\migrations;"
        return "app\\migrations\\" . $className;
    }
}
