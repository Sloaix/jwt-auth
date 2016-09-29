<?php

namespace Lsxiao\JWT\Exception;

class SecretKeyException extends BaseJWTException
{
    public function __construct($message = 'SecretKeyException')
    {
        parent::__construct($message);
    }

    public static function newNotFoundInstance($keyType = '')
    {
        return new SecretKeyException($keyType . ' secret key not found exception,you must specified a secret key in .env file');
    }

    public static function newInvalidInstance($keyType = '')
    {
        return new SecretKeyException($keyType . ' secret key is invalid');
    }
}