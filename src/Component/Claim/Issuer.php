<?php

namespace Lsxiao\JWT\Component\Claim;

class Issuer extends BaseClaim
{
    public function __construct($value)
    {
        parent::__construct("iss", $value);
    }


    function validate()
    {
        return true;
    }


}