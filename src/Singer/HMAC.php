<?php

namespace Lsxiao\JWT\Singer;


use Lsxiao\JWT\Contracts\ISign;
use Lsxiao\JWT\Exception\SecretKeyException;

class HMAC implements ISign
{
    /**
     * 算法map
     * @var array
     */
    public static $ALGORITHM_MAP = [
        'HS256' => 'sha256',
        'HS384' => 'sha384',
        'HS512' => 'sha512',
    ];

    /**
     * 算法id
     * @var string
     */
    private $algorithmId;
    /**
     * 默认的算法id
     * @var string
     */
    const DEFAULT_ALGO_ID = 'HS256';

    /**
     * HMAC 构造函数
     * @param string $algorithmId
     */
    public function __construct($algorithmId = self::DEFAULT_ALGO_ID)
    {
        $this->algorithmId = $algorithmId;
    }


    /**
     * 获取算法id list
     * @return string []
     */
    public static function getSupportAlgorithmIds()
    {
        return array_keys(HMAC::$ALGORITHM_MAP);
    }

    /**
     * 返回算法
     * @return string
     */
    public function getAlgorithm()
    {
        $algorithm = HMAC::$ALGORITHM_MAP[$this->algorithmId];
        if (!$algorithm) {
            $algorithm = HMAC::$ALGORITHM_MAP[self::DEFAULT_ALGO_ID];
        }
        return $algorithm;
    }

    /**
     * 签名
     * @param string $data
     * @param string $secretKey
     * @return string
     * @throws SecretKeyException
     */
    public function sign($data, $secretKey)
    {
        $this->validateSecretKey($secretKey);

        return hash_hmac($this->getAlgorithm(), $data, $secretKey);
    }

    /**
     * 验证签名
     * @param string $expects
     * @param string $data
     * @param string $secretKey
     * @return bool
     */
    public function verify($expects, $data, $secretKey)
    {
        return hash_equals($expects, $this->sign($data, $secretKey));
    }

    /**
     * 验证秘钥
     * @param $secretKey
     * @return bool
     * @throws SecretKeyException
     */
    public function validateSecretKey($secretKey)
    {
        if (!isset($secretKey)) {
            throw SecretKeyException::newInvalidInstance();
        }
        
        return true;
    }

}