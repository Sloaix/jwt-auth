<?php

namespace Lsxiao\JWT\Component\Claim;

class ClaimFactory
{

    /**
     * 创建Claim
     * @param $name
     * @param $value
     * @return Audience|ExpirationTime|IssuedAt|Issuer|JWTID|NotBefore|Subject
     */
    public static function create($name, $value)
    {
        if ($name == 'aud') {
            $claim = new Audience($value);
        } else if ($name == 'exp') {
            $claim = new ExpirationTime($value);
        } else if ($name == 'iat') {
            $claim = new IssuedAt($value);
        } else if ($name == 'iss') {
            $claim = new Issuer($value);
        } else if ($name == 'jti') {
            $claim = new JWTID($value);
        } else if ($name == 'nbf') {
            $claim = new NotBefore($value);
        } else if ($name == 'sub') {
            $claim = new Subject($value);
        } else if ($name == 'blgt') {
            $claim = new BlacklistGraceTime($value);
        } else if ($name == 'rexp') {
            $claim = new RefreshExpirationTime($value);
        } else {
            $claim = new CustomClaim($name, $value);
        }

        return $claim;
    }

}