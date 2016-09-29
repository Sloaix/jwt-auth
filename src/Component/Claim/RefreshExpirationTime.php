<?php

namespace Lsxiao\JWT\Component\Claim;

/**
 * 记录刷新次数
 * Class RefreshCount
 * @package Lsxiao\JWT\Component\Claim
 */
class RefreshExpirationTime extends BaseClaim
{
    public function __construct($value)
    {
        parent::__construct("rexp", $value);
    }

    function validate()
    {
        return is_int($this->getValue()) && $this->getValue() > 0;
    }
}