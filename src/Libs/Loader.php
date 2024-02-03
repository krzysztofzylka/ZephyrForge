<?php

namespace Zephyrforge\Zephyrforge\Libs;

use Zephyrforge\Zephyrforge\Libs\Form\Form;
use Zephyrforge\Zephyrforge\Libs\Table\Table;

class Loader
{

    /**
     * Load table instance
     * @return Table
     */
    public function table(): Table
    {
        return new Table();
    }

}