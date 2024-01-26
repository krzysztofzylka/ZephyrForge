<?php

namespace Zephyrforge\Zephyrforge;

use krzysztofzylka\DatabaseManager\Table;
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

    /**
     * Database column name or false
     * @var bool
     */
    public string|false $useTable;

    /**
     * Database table instance
     * @var Table
     */
    public Table $tableInstance;

}