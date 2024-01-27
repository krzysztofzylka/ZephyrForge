<?php

require('../../vendor/autoload.php');

try {
    ob_start();
    $kernel = new \Zephyrforge\Zephyrforge\Kernel();
    $kernel->init();
    $kernel->run();
    $content = ob_get_clean();
    $view = new \Zephyrforge\Zephyrforge\View();
    $view->render(\Zephyrforge\Zephyrforge\Kernel::$projectPath . '/templates/template.twig', ['content' => $content]);
} catch (Throwable $throwable) {
    var_dump($throwable);
}