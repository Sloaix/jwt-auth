<?php

namespace Lsxiao\JWT\Component\Claim;

class ExpirationTime extends BaseClaim
{
    public function __construct($value)
    {
        parent::__construct("exp", $value);
    }

    function validate()
    {
        return is_int($this->getValue()) && $this->getValue() > 0;
    }

}