<?php

namespace Lsxiao\JWT\Exception;

class TokenNotInRequestException extends BaseJWTException
{
    public function __construct($message = 'TokenNotInRequestException')
    {
        parent::__construct($message);
    }
}