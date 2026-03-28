<?php

require_once __DIR__ . '/vendor/autoload.php';

use framework\console\ConsoleApplication;

$app = ConsoleApplication::getInstance($argv);
$app->run();
