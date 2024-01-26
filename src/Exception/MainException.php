<?php

namespace Zephyrforge\Zephyrforge\Exception;

use Throwable;

/**
 * Main framework exception
 */
class MainException extends \Exception
{

    /**
     * Class constructor.
     * @param string $message The error message. Default is an empty string.
     * @param int $code The error code. Default is 500.
     * @param Throwable|null $previous The previous exception. Default is null.
     *
     * @return void
     */
    public function __construct(string $message = "", int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}