<?php

namespace Zephyrforge\Zephyrforge\Exception;

/**
 * Not found framework exception
 */
class NotFoundException extends MainException
{

    public function __construct(string $message = "Not found", int $code = 404, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}