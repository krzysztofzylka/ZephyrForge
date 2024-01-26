<?php

namespace Zephyrforge\Zephyrforge\Exception;

use Throwable;

/**
 * Hidden framework exception
 */
class HiddenException extends MainException
{

    /**
     * This variable is used to store a hidden message.
     * @var string $hiddenMessage
     */
    private string $hiddenMessage = '';

    /**
     * Constructor method for the class.
     * @param string $message [optional] The error message. Default is an empty string.
     * @param int $code [optional] The error code. Default is 0.
     * @param ?Throwable $previous [optional] The previous exception used for the exception chaining. Default is null.
     */
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        $this->hiddenMessage = $message;

        parent::__construct('Unexpected error occurred.', $code, $previous);
    }

    /**
     * Retrieves the hidden message.
     * @return string The hidden message.
     */
    public function getHiddenMessage(): string
    {
        return $this->hiddenMessage;
    }

}