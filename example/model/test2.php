<?php

namespace model;

use Zephyrforge\Zephyrforge\Model;

class test2 extends Model
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
                'name' => 'alt',
                'type' => 'longtext',
                'null' => true
            ]
        ];
    }

}