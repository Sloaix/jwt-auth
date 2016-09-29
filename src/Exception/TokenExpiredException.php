<?php

namespace Lsxiao\JWT\Exception;

class TokenExpiredException extends BaseJWTException
{
    public function __construct($message = 'TokenExpiredException')
    {
        parent::__construct($message);
    }
}