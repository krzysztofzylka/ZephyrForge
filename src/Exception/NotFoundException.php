<?php

namespace Zephyrforge\Zephyrforge\Exception;

use Throwable;

/**
 * Not found framework exception
 */
class NotFoundException extends MainException
{

    /**
     * Constructs a new instance of the class.
     * @param string $message The error message. Default: "Not found".
     * @param int $code The error code. Default: 404.
     * @param Throwable|null $previous The previous exception. Default: null.
     * @return void
     */
    public function __construct(string $message = "Not found", int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}