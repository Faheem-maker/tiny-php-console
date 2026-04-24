<?php

namespace framework\console\commands;

use framework\Application;
use framework\console\Command;

class ServeCommand extends Command
{
    public string $signature = 'serve';

    public function execute(Application $app, array $args): void
    {
        $host = '127.0.0.1';
        $port = 8000;
        $docRoot = getcwd() . DIRECTORY_SEPARATOR . 'public';

        echo "Starting PHP server at http://$host:$port\n";
        echo "Document root: $docRoot\n";
        echo "Press Ctrl+C to stop\n\n";

        $cmd = sprintf(
            'php -S %s:%d -t %s',
            $host,
            $port,
            escapeshellarg($docRoot)
        );

        // Passthru keeps output interactive (important!)
        passthru($cmd);

    }
}
