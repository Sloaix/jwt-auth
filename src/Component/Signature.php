<?php
namespace Lsxiao\JWT\Component;

class Signature
{
    private $value;

    /**
     * 构造函数
     * Signature constructor.
     * @param $value string
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}