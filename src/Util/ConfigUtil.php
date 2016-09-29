<?php
namespace Lsxiao\JWT\Util;

class ConfigUtil
{
    public static function getSecretKey()
    {
        return self::getConfig("secret_key");
    }

    public static function getTTL()
    {
        return self::getConfig("ttl");
    }

    public static function getRefreshTTL()
    {
        return self::getConfig("refresh_ttl");
    }

    public static function getBlackListGraceTime()
    {
        return self::getConfig("blacklist_grace_time");
    }

    public static function getAlgorithmId()
    {
        return self::getConfig("algorithm_id");
    }

    public static function getPublicSecretKey()
    {
        return self::getConfig("public_secret_key");
    }

    public static function getPrivateSecretKey()
    {
        return self::getConfig("private_secret_key");
    }

    public static function getNotBefore()
    {
        return self::getConfig("not_before");
    }


    private static function getConfig($name)
    {
        return config("jwt.$name");
    }
}