<?php

namespace controller;

use Zephyrforge\Zephyrforge\Controller;

class index extends Controller
{

    public function index(): void
    {
        $this->loadView();
    }

}