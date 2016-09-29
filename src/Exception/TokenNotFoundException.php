<?php

namespace Lsxiao\JWT\Exception;

class TokenNotFoundException extends BaseJWTException
{
    public function __construct($message = 'TokenNotFoundException')
    {
        parent::__construct($message);
    }
}