<?php

namespace Zephyrforge\Zephyrforge;

use Zephyrforge\Zephyrforge\Exception\MainException;
use Zephyrforge\Zephyrforge\Exception\NotFoundException;
use Zephyrforge\Zephyrforge\Libs\Loader;
use Zephyrforge\Zephyrforge\Libs\Model\LoadModel;
use Zephyrforge\Zephyrforge\Libs\Response;

/**
 * Controller
 */
class Controller
{

    use LoadModel;

    /**
     * Controller name
     * @var string
     */
    public string $name;

    /**
     * Controller action
     * @var string
     */
    public string $action;

    /**
     * Post data
     * @var ?array
     */
    public ?array $data;

    /**
     * Dialog title
     * @var string
     */
    public string $dialogTitle = '';

    /**
     * Dialog width
     * @var int
     */
    public int $dialogWidth = 600;

    /**
     * Response
     * @var Response
     */
    public Response $response;

    /**
     * Libs loader
     * @var Loader
     */
    public Loader $loader;

    /**
     * Load view
     * @param array $variables
     * @param string|null $action
     * @param bool|null $cache
     * @return void
     * @throws MainException
     * @throws NotFoundException
     */
    public function loadView(array $variables = [], string $action = null, ?bool $cache = null): void
    {
        $action = $action ?: $this->name . '/' . $this->action . '.twig';
        $viewPath = Kernel::$projectPath . '/src/view/' . $action;
        $view = new View(
            cache: $cache
        );

        View::$APP['dialog_config']['title'] = $this->dialogTitle;
        View::$APP['dialog_config']['width'] = $this->dialogWidth;

        $view->render(
            viewPath: $viewPath,
            variables: $variables
        );
    }

}