<?php
namespace Lsxiao\JWT\Component;

use Lsxiao\JWT\Component\Claim\BaseClaim;
use Lsxiao\JWT\Util\Base64Util;
use stdClass;

class Payload
{
    /**
     * @var BaseClaim[]
     */
    private $claims;

    /**
     * 构造函数
     * @param BaseClaim[] $claims
     */
    public function __construct($claims = [])
    {
        //去重
        $this->claims = array_unique($claims);
    }

    /**
     * 返回Claim数组
     * @return BaseClaim[]
     */
    public function getClaims()
    {
        return $this->claims;
    }

    /**
     * 根据name返回一个Claim
     * @param $name
     * @return BaseClaim
     */
    public function getClaim($name)
    {
        foreach ($this->claims as $c) {
            if ($c->getName() == $name) {
                return $c;
            }
        }
        return null;
    }

    /**
     * 增加一个Claim
     * @param BaseClaim $claim
     */
    public function addClaim(BaseClaim $claim)
    {
        foreach ($this->claims as $c) {
            //已经存在,直接返回
            if ($c == $claim) {
                return;
            } //存在相同name的claim,覆盖value后返回
            else if ($c->getName() == $claim->getName()) {
                $c->setValue($claim->getValue());
                return;
            }
        }
        //不存在,添加claim到array.
        array_push($this->claims, $claim);
    }

    /**
     * 输出Base64字符串
     * @return string
     */
    public function toBase64String()
    {
        return Base64Util::encode($this->toJsonString());
    }

    /**
     * 输出JSON字符串
     * @return string
     */
    public function toJsonString()
    {
        $object = new stdClass();
        foreach ($this->claims as $c) {
            $object->{$c->getName()} = $c->getValue();
        }
        return json_encode($object, JSON_UNESCAPED_SLASHES);
    }
}