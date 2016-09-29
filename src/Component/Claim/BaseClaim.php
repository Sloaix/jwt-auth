<?php

namespace Lsxiao\JWT\Component\Claim;

abstract class BaseClaim
{
    //名字
    protected $name;
    //值
    protected $value;

    /**
     * 构造函数
     * @param $name string
     * @param $value
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * 获取Claim的名字
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * 设置Claim的名字
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * 获取Claim的值
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * 设置Claim的值
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * 验证值是否正确
     * @return mixed
     */
    abstract function validate();

    /**
     * 方便后面Payload去重
     * @return string
     */
    function __toString()
    {
        return $this->name;
    }

}