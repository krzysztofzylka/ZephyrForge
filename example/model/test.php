<?php

namespace model;

use Zephyrforge\Zephyrforge\Model;

class test extends Model
{

    public function tableStructure(): array
    {
        return [
            [
                'name' => 'key',
                'type' => 'int',
                'length' => 11,
                'null' => false,
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
                'name' => 'description',
                'type' => 'text',
                'null' => true
            ],
            [
                'name' => 'date_created',
                'type' => 'datetime',
                'default' => 'NOW()',
                'default_function' => true
            ],
            [
                'name' => 'alt',
                'type' => 'text',
                'null' => true
            ],
        ];
    }

}