<?php
namespace Lsxiao\JWT\Contracts;

use Lsxiao\JWT\Component\Claim\BaseClaim;

interface IClaimProvider
{
    /**
     * 返回一个标识符(一般是user id),该标识符会被存储在subject claim 中.
     * @return string
     */
    public function getIdentifier();

    /**
     * 返回一个自定义Claim的数组,这些自定义Claim会被添加到token中
     * @return BaseClaim[]
     */
    public function getCustomClaims();
}