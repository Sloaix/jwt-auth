<?php
namespace Lsxiao\JWT;

use Lsxiao\JWT\Component\Claim\Audience;
use Lsxiao\JWT\Component\Claim\CustomClaim;
use Lsxiao\JWT\Component\Claim\ExpirationTime;
use Lsxiao\JWT\Component\Claim\IssuedAt;
use Lsxiao\JWT\Component\Claim\Issuer;
use Lsxiao\JWT\Component\Claim\JWTID;
use Lsxiao\JWT\Component\Claim\NotBefore;
use Lsxiao\JWT\Component\Claim\BlacklistGraceTime;
use Lsxiao\JWT\Component\Claim\RefreshExpirationTime;
use Lsxiao\JWT\Component\Claim\Subject;
use Lsxiao\JWT\Component\Header;
use Lsxiao\JWT\Component\Payload;
use Lsxiao\JWT\Exception\SecretKeyException;

/**
 *
 * Token Builder
 * @package Lsxiao\JWT
 */
class Builder
{
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
    /**
     * HMAC 私钥
     * @var string
     */
    private $secretKey;
    /**
     * RSA 私钥
     * @var string
     */
    private $privateKey;
    /**
     * RSA 公钥
     * @var string
     */
    private $publicKey;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->payload = new Payload();
        $this->header = new Header();
    }

    /**
     * HMAC 秘钥
     * @param $secretKey
     * @return $this
     */
    public function secretKey($secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }


    /**
     * RSA 私钥
     * @param mixed $privateKey
     * @return $this
     */
    public function privateKey($privateKey)
    {
        $this->privateKey = $privateKey;
        return $this;
    }

    /**
     * RSA 公钥
     * @param mixed $publicKey
     * @return $this
     */
    public function publicKey($publicKey)
    {
        $this->publicKey = $publicKey;
        return $this;
    }


    /**
     * 算法id
     * @param $algoId
     * @return $this
     */
    public function algoId($algoId)
    {
        $this->header->setAlgorithmId($algoId);
        return $this;
    }

    /**
     * 接收者
     * @param $aud
     * @return $this
     */
    public function audience($aud)
    {
        $this->payload->addClaim(new Audience($aud));
        return $this;
    }

    /**
     * 过期UNIX时间戳
     * @param $exp int
     * @return $this
     */
    public function expire($exp)
    {
        $this->payload->addClaim(new ExpirationTime($exp));
        return $this;
    }

    /**
     * 签发UNIX时间戳
     * @param $iat int
     * @return $this
     */
    public function issueAt($iat)
    {
        $this->payload->addClaim(new IssuedAt($iat));
        return $this;
    }

    /**
     * 签发者
     * @param $iss
     * @return $this
     */
    public function issuer($iss)
    {
        $this->payload->addClaim(new Issuer($iss));
        return $this;
    }

    /**
     * 指定UNIX时间戳,在这之前此TOKEN不可用
     * @param $nbf int
     * @return $this
     */
    public function notBefore($nbf)
    {
        $this->payload->addClaim(new NotBefore($nbf));
        return $this;
    }

    /**
     * 主题
     * @param $su
     * @return $this
     */
    public function subject($su)
    {
        $this->payload->addClaim(new Subject($su));
        return $this;
    }

    /**
     * JWT 唯一身份标识
     * @param $jti
     * @return $this
     */
    public function jwtId($jti)
    {
        $this->payload->addClaim(new JWTID($jti));
        return $this;
    }

    /**
     * 黑名单宽限时间
     * @param $rc
     * @return $this
     */
    public function blacklistGraceTime($rc)
    {
        $this->payload->addClaim(new BlacklistGraceTime($rc));
        return $this;
    }

    /**
     * 刷新过期时间
     * @param $rexp
     * @return $this
     * @internal param $rc
     */
    public function refreshExpire($rexp)
    {
        $this->payload->addClaim(new RefreshExpirationTime($rexp));
        return $this;
    }


    /**
     * 自定义Claim
     * @param $name string
     * @param $value
     * @return $this
     */
    public function customClaim($name, $value)
    {
        $this->payload->addClaim(new CustomClaim($name, $value));
        return $this;
    }

    public function build()
    {
        if ($this->header->isHMACAlgorithmId()) {
            if (empty($this->secretKey)) {
                throw new SecretKeyException('the sign algorithm is hmac,you must provide a secret key');
            }
        } else if ($this->header->isRSAAlgorithmId()) {
            if (empty($this->publicKey)) {
                throw new SecretKeyException('the sign algorithm is rsa,you must provide a public key');
            }
            if (empty($this->privateKey)) {
                throw new SecretKeyException('the sign algorithm is rsa,you must provide a private key');
            }
        }

        return new Token($this->header, $this->payload, $this->secretKey, $this->privateKey, $this->publicKey);
    }
}