<?php

namespace Lsxiao\JWT\Component\Claim;

class CustomClaim extends BaseClaim
{

    function validate()
    {
        return true;
    }

}