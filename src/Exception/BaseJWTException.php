<?php

namespace Lsxiao\JWT\Exception;

use Exception;

abstract class BaseJWTException extends Exception
{

    /**
     * BaseJWTException constructor.
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        return parent::__construct($message, $code, $previous);
    }

}