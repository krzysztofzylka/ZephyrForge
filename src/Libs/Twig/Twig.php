<?php

namespace Zephyrforge\Zephyrforge\Libs\Twig;

use Twig\Cache\FilesystemCache;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use Zephyrforge\Zephyrforge\Exception\MainException;
use Zephyrforge\Zephyrforge\Kernel;
use Zephyrforge\Zephyrforge\Libs\Log\Log;

/**
 * Twig libs
 */
class Twig
{

    /**
     * Twig file system loader instance
     * @var FilesystemLoader
     */
    public FilesystemLoader $twigFileSystemLoader;

    /**
     * Twig environment instance
     * @var Environment
     */
    public Environment $twigEnvironment;

    /**
     * Cache
     * @var bool
     */
    private bool $cache;

    /**
     * Constructor
     */
    public function __construct(?bool $cache = null)
    {
        $this->setCache($cache ?? $_ENV['TWIG_CACHE']);

        $this->twigFileSystemLoader = new FilesystemLoader();
        $this->addPath(Kernel::$projectPath . '/src/view');
        $this->twigEnvironment = new Environment($this->twigFileSystemLoader, [
            'cache' => Kernel::$projectPath . '/storage/cache/twig',
        ]);

        if ($this->isCache()) {
            $cache = new FilesystemCache(
                directory: Kernel::$projectPath . '/storage/cache/twig'
            );

            $this->twigEnvironment->setCache($cache);
        } else {
            $this->twigEnvironment->setCache(false);
        }
    }

    /**
     * Render view
     * @param string $twigFilePath
     * @param array $variables
     * @return string
     * @throws MainException
     */
    public function render(string $twigFilePath, array $variables = []): string
    {
        try {
            $this->addPath(__DIR__ . '/Files/');
            $this->addPath(dirname($twigFilePath));

            return $this->twigEnvironment->render(basename($twigFilePath), $variables);
        } catch (\Throwable $throwable) {
            Log::throwableLog($throwable);

            throw new MainException($throwable->getMessage(), $throwable->getCode() ?? 500);
        }
    }

    /**
     * @param $path
     * @return void
     * @throws LoaderError
     */
    public function addPath($path): void
    {
        $this->twigFileSystemLoader->addPath($path);
    }

    /**
     * @param $path
     * @return void
     */
    public function setPaths($path): void
    {
        $this->twigFileSystemLoader->setPaths($path);
    }

    /**
     * Cache is active
     * @return bool
     */
    public function isCache(): bool
    {
        return $this->cache;
    }

    /**
     * Change twig cache
     * @param bool $cache
     * @return void
     */
    public function setCache(bool $cache): void
    {
        $this->cache = $cache;
    }

}