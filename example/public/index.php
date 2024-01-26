<?php

require('../../vendor/autoload.php');

try {
    $kernel = new \Zephyrforge\Zephyrforge\Kernel();
    $kernel->run();
} catch (Throwable $throwable) {
    var_dump($throwable);
}

var_dump($_ENV);