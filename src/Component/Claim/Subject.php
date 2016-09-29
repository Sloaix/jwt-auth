<?php

namespace Lsxiao\JWT\Component\Claim;

class Subject extends BaseClaim
{
    public function __construct($value)
    {
        parent::__construct("sub", $value);
    }

    function validate()
    {
        return true;
    }

}