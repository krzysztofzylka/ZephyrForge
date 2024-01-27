<?php

namespace src\model;

use Zephyrforge\Zephyrforge\Model;

class test extends Model
{

    public function tableStructure(): array
    {
        return [
            [
                'name' => 'id',
                'type' => 'int',
                'auto_increment' => true,
                'primary_key' => true
            ],
            [
                'name' => 'name',
                'type' => 'varchar',
                'length' => 255,
                'null' => true
            ],
            [
                'name' => 'value',
                'type' => 'text',
                'null' => true
            ],
            [
                'name' => 'date_created',
                'type' => 'datetime',
                'default' => 'NOW()',
                'default_function' => true
            ]
        ];
    }

}