<?php

namespace Zephyrforge\Zephyrforge;

use Zephyrforge\Zephyrforge\Libs\Model\LoadModel;

/**
 * Model
 */
class Model
{

    use LoadModel;

    /**
     * Model name
     * @var string
     */
    public string $name;

    /**
     * Controller instance
     * @var Controller
     */
    public Controller $controller;

}