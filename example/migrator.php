<?php

use Krzysztofzylka\File\File;
use Zephyrforge\Zephyrforge\Kernel;
use Zephyrforge\Zephyrforge\Libs\Migrator\Migrator;

require('../vendor/autoload.php');

Kernel::$projectPath = __DIR__;
Kernel::$silent = true;
$kernel = new Kernel();
$kernel->init();
File::mkdir(Kernel::$projectPath . '/migrations');

$migrator = new Migrator();
$migrator->runMigrations();
$migrator->createMigrations();
$migrator->runMigrations();