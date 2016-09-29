<?php

namespace Lsxiao\JWT\Component\Claim;

class JWTID extends BaseClaim
{
    public function __construct($value)
    {
        parent::__construct("jti", $value);
    }


    function validate()
    {
        return true;
    }

}