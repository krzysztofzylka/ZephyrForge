<?php

namespace Zephyrforge\Zephyrforge;


use Throwable;
use Zephyrforge\Zephyrforge\Exception\MainException;
use Zephyrforge\Zephyrforge\Exception\NotFoundException;
use Zephyrforge\Zephyrforge\Libs\Log\Log;
use Zephyrforge\Zephyrforge\Libs\Twig\Twig;

/**
 * View class
 */
class View
{

    /**
     * Global app variables
     * @var array
     */
    public static array $APP = [
        'here' => null,
        'dialog_config' => [
            'title' => null,
            'width' => null
        ]
    ];

    /**
     * Twig instance
     * @var Twig
     */
    private Twig $twig;

    /**
     * Constructor
     * @param bool|null $cache
     */
    public function __construct(bool $cache = null)
    {
        $this->twig = new Twig(
            cache: $cache
        );
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

        $variables = array_merge(
            $variables,
            [
                'APP' => array_merge(
                    self::$APP,
                    [
                        'here' => $_SERVER['REQUEST_URI'],
                        'dialog_config' => json_encode(self::$APP['dialog_config'])
                    ]
                )
            ]
        );

        try {
            echo $this->twig->render($viewPath, $variables);
        } catch (Throwable $throwable) {
            Log::throwableLog($throwable);

            throw new MainException($throwable->getMessage(), $throwable->getCode() ?? 500);
        }
    }

}