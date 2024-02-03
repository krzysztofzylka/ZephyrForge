<?php

namespace Zephyrforge\Zephyrforge\Libs\Table;

class RenderAction
{
    /**
     * Generate action
     * @param Table $tableInstance
     * @param string $action
     * @param array $params
     * @return string
     */
    public static function generate(Table $tableInstance, string $action, array $params = []): string
    {
        $data = [
            'layout' => 'table',
            'here' => $_SERVER['REQUEST_URI'],
            'id' => $tableInstance->getId(),
            'action' => $action,
            'params' => $params

        ];

        return json_encode($data);
    }
}