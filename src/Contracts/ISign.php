<?php
namespace Lsxiao\JWT\Contracts;

interface ISign
{
    /**
     * 返回算法
     * @return string|int
     */
    public function getAlgorithm();

    /**
     * 返回支持的算法id列表
     * @return string []
     */
    public static function getSupportAlgorithmIds();

    /**
     * 签名
     * @param $data string 数据
     * @param $secretKey string 秘钥
     * @return string
     */
    public function sign($data, $secretKey);

    /**
     * 验证签名
     * @param $expects string 预期结果
     * @param $data string 待验证数据
     * @param $secretKey string 秘钥
     * @return bool
     */
    public function verify($expects, $data, $secretKey);

    /**
     * 验证秘钥是否合法
     * @param $secretKey
     * @return bool
     */
    public function validateSecretKey($secretKey);
}