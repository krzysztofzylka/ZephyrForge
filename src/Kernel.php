<?php

namespace Zephyrforge\Zephyrforge;

use Krzysztofzylka\File\File;
use Zephyrforge\Zephyrforge\Exception\MainException;
use Zephyrforge\Zephyrforge\Exception\NotFoundException;
use Zephyrforge\Zephyrforge\Libs\Log\Log;

/**
 * Kernel
 */
class Kernel
{

    /**
     * The path to the project directory.
     * @var string
     */
    public static string $projectPath;


    /**
     * Disable create project default directories
     * @var bool $silent
     */
    public static bool $silent = false;

    /**
     * Kernel is initialized
     * @var bool
     */
    private static bool $init = false;

    /**
     * Start kernel
     * @return void
     * @throws MainException
     */
    public function run(): void
    {
        try {
            $this->defineProjectPath();
            $this->autoload();

            if (!Kernel::$silent) {
                File::mkdir([
                    self::$projectPath . '/model',
                    self::$projectPath . '/controller',
                    self::$projectPath . '/view',
                    self::$projectPath . '/storage',
                    self::$projectPath . '/storage/logs',
                    self::$projectPath . '/storage/cache',
                    self::$projectPath . '/storage/cache/twig',
                    self::$projectPath . '/public'
                ], 0775);
            }
        } catch (\Throwable $throwable) {
            Log::throwableLog($throwable);

            throw new MainException($throwable->getMessage(), 500, $throwable);
        }

        $action = $this->getAction();
        $controllerClass = '\\controller\\' . $action['controller'];

        if (!class_exists($controllerClass)) {
            Log::log('Controller not found', 'WARNING', ['action' => $action, 'controllerClass' => $controllerClass]);

            throw new NotFoundException('Controller not found');
        }

        try {
            /** @var Controller $controllerClass */
            $controller = new $controllerClass();

            if (!method_exists($controller, $action['method'])) {
                throw new NotFoundException('Method not found');
            }

            $controller->name = $action['controller'];
            $controller->action = $action['method'];

            $controller->{$action['method']}(...$action['parameters']);
        } catch (\Throwable $throwable) {
            Log::throwableLog($throwable);

            throw new MainException($throwable->getMessage(), 500, $throwable);
        }

        Kernel::$init = true;
    }

    /**
     * Retrieve the current action from the URL parameters.
     * @return array
     */
    private function getAction(): array
    {
        $getActions = explode('/', htmlspecialchars($_GET['action']), 3);

        return [
                'controller' => $getActions[0] ?: 'index',
                'method' => $getActions[1] ?: 'index',
                'parameters' => isset($getActions[2]) ? explode('/', $getActions[2]) : []
            ];
    }

    /**
     * Define the project path.
     * @return void
     */
    private function defineProjectPath(): void
    {
        if (!isset($this->projectPath)) {
            self::$projectPath = $this->determineProjectPath();
        }

    }

    /**
     * Determine the project path.
     * @return string The project path.
     */
    private function determineProjectPath(): string
    {
        return realpath(dirname(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[2]['file']) . '/../');
    }

    /**
     * Autoload project classes
     * @return void
     * @throws NotFoundException
     */
    private function autoload(): void
    {
        if (Kernel::$init) {
            return;
        }

        spl_autoload_register(function ($class_name) {
            $path = File::repairPath(self::$projectPath . DIRECTORY_SEPARATOR . $class_name . '.php');

            if (!file_exists($path)) {
                throw new NotFoundException('Class not found');
            }

            include($path);
        });
    }

}