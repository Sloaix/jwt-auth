<?php

namespace Lsxiao\JWT\Singer;


use Lsxiao\JWT\Contracts\ISign;
use Lsxiao\JWT\Exception\SecretKeyException;

class RSA implements ISign
{
    /**
     * 算法map
     * @var array
     */
    private $algorithmMap;
    /**
     * 算法id
     * @var string
     */
    private $algorithmId;
    /**
     * 默认算法id
     * @var string
     */
    const DEFAULT_ALGO_ID = 'RS256';

    /**
     * RSA 构造器.
     * @param string $algorithmId
     */
    public function __construct($algorithmId = self::DEFAULT_ALGO_ID)
    {
        $this->algorithmMap = [
            'RS256' => OPENSSL_ALGO_SHA256,
            'RS384' => OPENSSL_ALGO_SHA384,
            'RS512' => OPENSSL_ALGO_SHA512,
        ];

        $this->algorithmId = $algorithmId;
    }

    /**
     * 返回算法
     * @return string
     */
    public function getAlgorithm()
    {
        $algorithm = $this->algorithmMap[$this->algorithmId];
        if (!$algorithm) {
            $algorithm = $this->algorithmMap[self::DEFAULT_ALGO_ID];
        }
        return $algorithm;
    }

    /**
     * 签名
     * @param string $data
     * @param string $privateKey
     * @return string
     */
    public function sign($data, $privateKey)
    {
        $privateKeyRes = openssl_pkey_get_private($privateKey);

        $this->validateSecretKey($privateKeyRes);

        openssl_sign($data, $signature, $privateKeyRes, $this->getAlgorithm());

        return $signature;
    }

    /**
     * 验证
     * @param string $expects
     * @param string $data
     * @param string $publicKey
     * @return bool
     */
    public function verify($expects, $data, $publicKey)
    {
        $publicKeyRes = openssl_get_publickey($publicKey);

        $this->validateSecretKey($publicKeyRes);

        return openssl_verify($data, $expects, $publicKeyRes, $this->getAlgorithm()) === 1;
    }


    /**
     * 验证秘钥
     * @param $secretKey
     * @return mixed|void
     * @throws SecretKeyException
     */
    public function validateSecretKey($secretKey)
    {
        if ($secretKey === false) {
            throw new SecretKeyException(
                'your rsa key can not be parse, because: ' . openssl_error_string()
            );
        }
        $details = openssl_pkey_get_details($secretKey);
        if (!isset($details['key']) || $details['type'] !== OPENSSL_KEYTYPE_RSA) {
            throw new SecretKeyException('This  key is not compatible with RSA signatures');
        }
    }

}