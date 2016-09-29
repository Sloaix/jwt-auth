<?php

namespace Lsxiao\JWT\Component\Claim;

class NotBefore extends BaseClaim
{
    public function __construct($value)
    {
        parent::__construct("nbf", $value);
    }

    function validate()
    {
        return is_int($this->getValue()) && $this->getValue() > 0;
    }

}