<?php

namespace Zephyrforge\Zephyrforge\Exception;

use Throwable;

/**
 * Main framework exception
 */
class MainException extends \Exception
{

    public function __construct(string $message = "", int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}