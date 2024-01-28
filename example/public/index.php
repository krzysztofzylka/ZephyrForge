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
    $codeMessage = match($throwable->getCode()) {
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        204 => 'No Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        304 => 'Not Modified',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        503 => 'Service Unavailable',
        default => 'Unknown error',
    };

    $view = new \Zephyrforge\Zephyrforge\View();
    $view->render(\Zephyrforge\Zephyrforge\Kernel::$projectPath . '/templates/error.twig', ['message' => $codeMessage, 'code' => $throwable->getCode()]);
}