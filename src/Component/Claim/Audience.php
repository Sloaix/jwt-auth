<?php

namespace Lsxiao\JWT\Component\Claim;

class Audience extends BaseClaim
{
    public function __construct($value)
    {
        parent::__construct("aud", $value);
    }

    function validate()
    {
        return true;
    }

}