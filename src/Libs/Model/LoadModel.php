<?php

namespace Zephyrforge\Zephyrforge\Libs\Model;

use krzysztofzylka\DatabaseManager\Table;
use Krzysztofzylka\Strings\Strings;
use Zephyrforge\Zephyrforge\Controller;
use Zephyrforge\Zephyrforge\Exception\NotFoundException;
use Zephyrforge\Zephyrforge\Libs\Log\Log;
use Zephyrforge\Zephyrforge\Model;

/**
 * LoadModel
 */
trait LoadModel
{

    /**
     * Models
     * @var array
     */
    public array $models = [];

    /**
     * Load model
     * @param string $name
     * @return Model
     * @throws NotFoundException
     */
    public function loadModel(string $name): Model
    {
        $className = '\\model\\' . $name;

        if (!class_exists($className)) {
            Log::log('Model ' . $name . ' not found', 'ERROR');

            throw new NotFoundException('Model not found');
        }

        /** @var Model $className */
        $model = new $className();
        $model->name = $name;
        $model->controller = $this instanceof Controller ? $this : $this->controller;

        if ($_ENV['DATABASE']) {
            if (!isset($model->useTable)) {
                $model->useTable = $name;
            }

            if (isset($model->useTable) && is_string($model->useTable)) {
                $model->tableInstance = (new Table($model->useTable));
            }
        }

        $this->models[Strings::camelizeString($name, '_')] = $model;

        return $model;
    }


    /**
     * Magic __get
     * @param string $name
     * @return mixed|Model
     */
    public function __get(string $name): mixed
    {
        if (in_array($name, array_keys($this->models))) {
            return $this->models[$name];
        }

        return trigger_error(
            'Undefined model',
            E_USER_WARNING
        );
    }

}