<?php

namespace Zephyrforge\Zephyrforge;

use Exception;
use krzysztofzylka\DatabaseManager\DatabaseConnect;
use krzysztofzylka\DatabaseManager\DatabaseManager;
use Krzysztofzylka\DatabaseManager\Enum\DatabaseType;
use krzysztofzylka\DatabaseManager\Exception\DatabaseManagerException;
use Krzysztofzylka\Env\Env;
use Krzysztofzylka\File\File;
use Throwable;
use Zephyrforge\Zephyrforge\Exception\HiddenException;
use Zephyrforge\Zephyrforge\Exception\MainException;
use Zephyrforge\Zephyrforge\Exception\NotFoundException;
use Zephyrforge\Zephyrforge\Libs\Log\Log;
use Zephyrforge\Zephyrforge\Libs\Response;

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
     * Initialize the project.
     * This method defines the project path, autoloads the necessary files, creates the required directory structure and
     * loads the environment configuration. It also connects to the database.
     * @return void
     * @throws MainException When an error occurs during initialization.
     * @throws Exception
     */
    public function init(): void
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
                    self::$projectPath . '/public',
                    self::$projectPath . '/migrations'
                ], 0775);
                File::touch(self::$projectPath . '/.env');
                File::copy(__DIR__ . '/Libs/Migrator/Other/migrator.php', Kernel::$projectPath . '/migrator.php');
            }
        } catch (Throwable $throwable) {
            Log::throwableLog($throwable);

            throw new MainException($throwable->getMessage(), 500, $throwable);
        }

        $this->loadEnv();
        $this->connectDatabase();
    }

    /**
     * Start kernel
     * @return void
     * @throws MainException
     * @throws Exception
     */
    public function run(): void
    {
        $action = $this->getAction();
        $controllerClass = '\\controller\\' . $action['controller'];

        if (!class_exists($controllerClass)) {
            $filePath = self::$projectPath . '/public/' . htmlspecialchars($_GET['action']);

            if (file_exists($filePath)) {
                (new Response())->fileContents($filePath);
            }

            throw new NotFoundException('Not found');
        }

        try {
            /** @var Controller $controllerClass */
            $controller = new $controllerClass();

            if (!method_exists($controller, $action['method'])) {
                throw new NotFoundException('Method not found');
            }

            $controller->name = $action['controller'];
            $controller->action = $action['method'];
            $controller->response = new Response();

            $controller->{$action['method']}(...$action['parameters']);
        } catch (Throwable $throwable) {
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
        if (!isset(self::$projectPath)) {
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

            include($path);
        });
    }

    /**
     * Load the environment variables.
     * @return void
     * @throws Exception
     */
    private function loadEnv(): void
    {
        if (Kernel::$init) {
            return;
        }

        try {
            $env = new Env([
                __DIR__ . '/Libs/Env/.env',
                self::$projectPath . '/.env'
            ]);

            $env->load();
        } catch (Throwable $throwable) {
            Log::throwableLog($throwable);

            throw new MainException($throwable->getMessage(), 500, $throwable);
        }
    }

    /**
     * Connect to the database.
     * This method connects to the database using the provided database configuration
     * parameters from the environment variables.
     * @throws HiddenException If a DatabaseManagerException occurs, it is caught, logged,
     *                         and rethrown as a HiddenException.
     * @throws MainException   If any other exception or error occurs, it is caught, logged,
     *                         and rethrown as a MainException with a status code of 500.
     */
    private function connectDatabase(): void
    {
        if (!$_ENV['DATABASE']) {
            return;
        }

        $connection = DatabaseConnect::create()
            ->setType(
                match ($_ENV['DATABASE_DRIVER']) {
                    'mysql' => DatabaseType::mysql,
                    'sqlite' => DatabaseType::sqlite
                }
            )
            ->setCharset($_ENV['DATABASE_CHARSET'])
            ->setHost($_ENV['DATABASE_HOST'])
            ->setDatabaseName($_ENV['DATABASE_NAME'])
            ->setPassword($_ENV['DATABASE_PASSWORD'])
            ->setUsername($_ENV['DATABASE_USERNAME'])
            ->setPort($_ENV['DATABASE_PORT']);

        try {
            $manager = new DatabaseManager();
            $manager->connect($connection);
        } catch (DatabaseManagerException $exception) {
            Log::throwableLog($exception);

            throw new HiddenException($exception->getHiddenMessage() ?: $exception->getMessage(), $exception->getCode(), $exception);
        } catch (Throwable $throwable) {
            Log::throwableLog($throwable);

            throw new MainException($throwable->getMessage(), 500, $throwable);
        }
    }

}