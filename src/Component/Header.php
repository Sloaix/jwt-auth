<?php
namespace Lsxiao\JWT\Component;

use Lsxiao\JWT\Singer\HMAC;
use Lsxiao\JWT\Util\Base64Util;
use stdClass;

class Header
{
    //声明类型
    private $type;
    //声明算法id
    private $algorithmId;

    /**
     * 构造函数
     * Header constructor.
     * @param string $type
     * @param string $algorithmId
     */
    public function __construct($algorithmId = HMAC::DEFAULT_ALGO_ID, $type = 'JWT')
    {
        $this->type = $type;
        $this->algorithmId = $algorithmId;
    }


    /**
     * 返回type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 设置type
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * 返回算法id
     * @return string
     */
    public function getAlgorithmId()
    {
        return $this->algorithmId;
    }


    public function isHMACAlgorithmId()
    {
        if (starts_with($this->algorithmId, 'H')) {
            return true;
        }
        return false;
    }

    public function isRSAAlgorithmId()
    {
        if (starts_with($this->algorithmId, 'R')) {
            return true;
        }
        return false;
    }


    /**
     * 设置算法id
     * @param string $algorithmId
     */
    public function setAlgorithmId($algorithmId)
    {
        $this->algorithmId = $algorithmId;
    }


    /**
     * 输出Base64字符串
     * @return string
     */
    public function toBase64String()
    {
        //Base64Util是一个url安全的Base64Util,后面会提及到
        return Base64Util::encode($this->toJsonString());
    }

    /**
     * 输出JSON字符串
     * @return string
     */
    public function toJsonString()
    {
        $object = new stdClass();
        $object->typ = $this->type;
        $object->alg = $this->algorithmId;
        return json_encode($object);
    }
}