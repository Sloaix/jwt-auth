<?php
namespace Lsxiao\JWT;

use DateTime;
use InvalidArgumentException;
use Lsxiao\JWT\Component\Header;
use Lsxiao\JWT\Component\Payload;
use Lsxiao\JWT\Component\Signature;
use Lsxiao\JWT\Exception\TokenExpiredException;
use Lsxiao\JWT\Exception\TokenInvalidException;
use Lsxiao\JWT\Singer\HMAC;
use Lsxiao\JWT\Singer\RSA;
use Lsxiao\JWT\Singer\SingerFactory;
use Lsxiao\JWT\Util\Base64Util;
use Lsxiao\JWT\Util\CacheUtil;

class Token
{
    use TokenTrait;
    /**
     * 载荷
     * @var Payload
     */
    private $payload;
    /**
     * 头部
     * @var Header
     */
    private $header;
    /*
     * HMAC 秘钥
     * @var string
     */
    private $secretKey;
    /**
     * RSA私钥
     * @var string
     */
    private $privateKey;
    /**
     * RSA公钥
     * @var string
     */
    private $publicKey;
    /**
     * 需要校验的Signature,只有在通过Parser创建的时候才需要指定
     * @var Signature
     */
    private $needVerifySignature;

    /**
     * Token 构造器
     * @param $payload Payload 载荷
     * @param $header Header 头部
     * @param $secretKey string HMAC秘钥
     * @param $privateKey string RSA私钥
     * @param $publicKey string RSA公钥
     * @param $needVerifySignature Signature 需要验证的token,通过Parser解析创建Token时此参数不能为空
     */
    public function __construct(Header $header, Payload $payload, $secretKey, $privateKey, $publicKey, Signature $needVerifySignature = null)
    {
        $this->payload = $payload;
        $this->header = $header;
        $this->secretKey = $secretKey;
        $this->privateKey = $privateKey;
        $this->publicKey = $publicKey;
        $this->needVerifySignature = $needVerifySignature;
    }

    /**
     * 是不是通过Parser解析创建的Token
     * @return bool
     */
    private function isParsedToken()
    {
        return isset($this->needVerifySignature);
    }

    /**
     * token 是否过期
     * @return bool
     */
    public function isExpired()
    {
        $expClaim = $this->payload->getClaim('exp');

        if (!$expClaim) {
            return true;
        }

        $now = new DateTime();

        $expiredAt = new DateTime();

        $expiredAt->setTimestamp($expClaim->getValue());

        return $now > $expiredAt;
    }

    /**
     * token 刷新是否过期
     * @return bool
     */
    public function isRefreshExpired()
    {
        $expClaim = $this->payload->getClaim('rexp');

        if (!$expClaim) {
            return true;
        }

        $now = new DateTime();

        $expiredAt = new DateTime();

        $expiredAt->setTimestamp($expClaim->getValue());

        return $now > $expiredAt;
    }

    /**
     * 如果存在于黑名单中,是否处于黑名单宽限时间内
     * 如果不存在于黑名单中,直接返回true
     * @return bool
     */
    public function hasBlacklistGraceTimeOrNotInBlacklist()
    {
        //如果Token不是由解析生成的,那么没必要验证黑名单宽限时间,因为此时Token并没有被加入到黑名单中
        if (!$this->isParsedToken()) {
            return true;
        }

        //如果不存在于黑名单中,没必要验证宽限时间
        if (!$this->isInBlacklist()) {
            return true;
        }
        $jwtId = $this->getClaim('jti')->getValue();
        $blacklistGraceTime = $this->payload->getClaim('blgt')->getValue();
        $startTimestamp = CacheUtil::getBlackListCacheStartTime($jwtId);
        $now = time();
        return $now < $startTimestamp + $blacklistGraceTime;
    }

    /**
     * 是否存在于黑名单中
     */
    public function isInBlacklist()
    {
        $jwtId = $this->getClaim('jti')->getValue();

        return CacheUtil::isInBlackList($jwtId);
    }

    /**
     * 根据name返回Claim
     * @param $name
     * @return Component\Claim\BaseClaim
     */
    public function getClaim($name)
    {
        return $this->payload->getClaim($name);
    }

    /**
     * 返回payload
     * @return Payload
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * token 是否可用
     * @return bool
     */
    public function isAvailable()
    {
        $nbfClaim = $this->payload->getClaim('nbf');

        if (!isset($nbfClaim)) {
            return true;
        }

        $now = new DateTime();

        $notBefore = new DateTime();

        $notBefore->setTimestamp($nbfClaim->getValue());

        return $now > $notBefore;
    }

    /**
     * token 是否有效
     * @return bool
     */
    public function isValid()
    {
        return !$this->isExpired() && $this->hasBlacklistGraceTimeOrNotInBlacklist() && $this->isAvailable() && $this->verify();
    }

    /**
     * token 是否能够刷新
     * @return bool
     */
    public function canRefresh()
    {
        return !$this->isRefreshExpired() && !$this->isInBlacklist() && $this->isAvailable() && $this->verify();
    }

    /**
     * 验证token 如果有问题则抛出异常
     * @throws TokenExpiredException
     * @throws TokenInvalidException
     */
    public function validate()
    {
        /**
         * 在黑名单中
         */
        if (!$this->hasBlacklistGraceTimeOrNotInBlacklist()) {
            throw new TokenInvalidException("the token is in blacklist");
        }

        /**
         * token过期
         */
        if ($this->isExpired()) {
            throw new TokenExpiredException("the token is expired");
        }

        /**
         * 签名无效
         */
        if (!$this->verify()) {
            throw new TokenInvalidException("the signature is invalid");
        }

        /**
         * 不可用
         */
        if (!$this->isAvailable()) {
            throw new TokenInvalidException('NotBefore Claim (nbf) timestamp is in the future');
        }
    }

    /**
     * 刷新验证 如果有问题抛出异常
     * @throws TokenInvalidException
     */
    public function refreshValidate()
    {
        /**
         * 在黑名单中
         */
        if ($this->isInBlacklist()) {
            throw new TokenInvalidException("the token is in blacklist");
        }

        /**
         * token refresh过期
         */
        if ($this->isRefreshExpired()) {
            throw new TokenInvalidException("the token refresh time is expired");
        }

        /**
         * 签名无效
         */
        if (!$this->verify()) {
            throw new TokenInvalidException("the signature is invalid");
        }

        /**
         * 不可用
         */
        if (!$this->isAvailable()) {
            throw new TokenInvalidException('NotBefore Claim (nbf) timestamp is in the future');
        }
    }


    /**
     * 验证签名
     * @return bool|mixed
     */
    public function verify()
    {
        //如果Token不是由解析生成的,那么没必要验证签名是否有效,因为验证用的签名不存在
        if (!$this->isParsedToken()) {
            return true;
        }

        $data = $this->getHeaderConcatPayloadString();
        $signer = $this->getSigner();
        if ($signer instanceof HMAC) {
            return $signer->verify($this->needVerifySignature->getValue(), $data, $this->secretKey);
        } else if ($signer instanceof RSA) {
            return $signer->verify($this->needVerifySignature->getValue(), $data, $this->publicKey);
        }
        return false;
    }

    /**
     * 返回 header . payload 字符串
     * @return string
     */
    public function getHeaderConcatPayloadString()
    {
        return $this->header->toBase64String() . "." . $this->payload->toBase64String();
    }

    /**
     * 返回签名者
     * @return Contracts\ISign
     */
    public function getSigner()
    {
        $algorithmId = $this->header->getAlgorithmId();
        $signer = SingerFactory::createByAlgorithmId($algorithmId);
        return $signer;
    }

    /**
     * 计算signature
     *
     * @return Signature|null
     */
    public function calculateSignature()
    {
        $data = $this->getHeaderConcatPayloadString();
        $signer = $this->getSigner();
        if ($signer instanceof HMAC) {
            return new Signature($signer->sign($data, $this->secretKey));
        } else if ($signer instanceof RSA) {
            return new Signature($signer->sign($data, $this->publicKey));
        } else {
            throw new InvalidArgumentException("the signer is invalid");
        }
    }

    /**
     * 返回用于验证的Signature 值
     * @return Signature|null
     */
    public function getNeedVerifySignature()
    {
        return $this->needVerifySignature;
    }


    /**
     * 返回Token字符串
     * @return string
     */
    public function toString()
    {
        $realSignature = $this->calculateSignature();

        $value = Base64Util::encode($realSignature->getValue());

        return $this->getHeaderConcatPayloadString() . '.' . $value;
    }

}