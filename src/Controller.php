<?php

namespace Zephyrforge\Zephyrforge;

use Zephyrforge\Zephyrforge\Libs\Model\LoadModel;

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
     * Load view
     * @param array $variables
     * @param string|null $action
     * @return void
     * @throws Exception\MainException
     * @throws Exception\NotFoundException
     */
    public function loadView(array $variables = [], string $action = null): void
    {
        $action = $action ?: $this->name . '/' . $this->action . '.twig';
        $viewPath = Kernel::$projectPath . '/view/' . $action;
        $view = new View();

        $view->render(
            viewPath: $viewPath,
            variables: $variables
        );
    }

}