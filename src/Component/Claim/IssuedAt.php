<?php

namespace Lsxiao\JWT\Component\Claim;

class IssuedAt extends BaseClaim
{
    public function __construct($value)
    {
        parent::__construct("iat", $value);
    }


    function validate()
    {
        return is_int($this->getValue()) && $this->getValue() > 0;
    }

}