<?php

namespace Zephyrforge\Zephyrforge;


use Zephyrforge\Zephyrforge\Exception\MainException;
use Zephyrforge\Zephyrforge\Exception\NotFoundException;
use Zephyrforge\Zephyrforge\Libs\Log\Log;
use Zephyrforge\Zephyrforge\Libs\Twig\Twig;
use Throwable;

/**
 * View class
 */
class View
{

    /**
     * Twig instance
     * @var Twig
     */
    private Twig $twig;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->twig = new Twig();
    }

    /**
     * Render view path
     * @param string $viewPath
     * @param array $variables
     * @return void
     * @throws MainException
     * @throws NotFoundException
     */
    public function render(
        string $viewPath,
        array $variables = []
    ): void
    {
        if (!file_exists($viewPath)) {
            Log::log('View not found', 'WARNING', ['viewPath' => $viewPath]);

            throw new NotFoundException('View not found');
        }

        try {
            echo $this->twig->render($viewPath, $variables);
        } catch (Throwable $throwable) {
            Log::throwableLog($throwable);

            throw new MainException($throwable->getMessage(), $throwable->getCode() ?? 500);
        }
    }

}