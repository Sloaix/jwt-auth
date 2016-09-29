<?php

namespace Lsxiao\JWT\Util;


class CacheUtil
{
    const PREFIX = "jwt:blacklist:jti:";

    /**
     * 是否在黑名单中
     * @param $jti string Token唯一身份标识
     * @return $this
     */
    public static function isInBlackList($jti)
    {
        return self::instance()->has(self::PREFIX . $jti);
    }

    /**
     * 获取黑名单缓存开始时间
     * @param $jti string Token唯一身份标识
     * @return int
     */
    public static function getBlackListCacheStartTime($jti)
    {
        return self::instance()->get(self::PREFIX . $jti);
    }

    /**
     * 添加token到黑名单
     * @param $jti string JWT唯一身份标识
     * @param $minutes int
     */
    public static function addToBlacklist($jti, $minutes)
    {
        //缓存开始时间
        $startTimestamp = time();
        self::instance()->put(self::PREFIX . $jti, $startTimestamp, $minutes);
    }

    public static function instance()
    {
        return app('cache');
    }

}