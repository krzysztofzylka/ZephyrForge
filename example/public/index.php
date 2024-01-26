<?php

require('../../vendor/autoload.php');

try {
    $kernel = new \Zephyrforge\Zephyrforge\Kernel();
    $kernel->init();
    $kernel->run();
} catch (Throwable $throwable) {
    var_dump($throwable);
}