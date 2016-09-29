<?php

namespace Lsxiao\JWT\Util;


use Lsxiao\JWT\Component\Claim\ClaimFactory;
use Lsxiao\JWT\Component\Header;
use Lsxiao\JWT\Component\Payload;
use Lsxiao\JWT\Component\Signature;
use Lsxiao\JWT\Exception\SecretKeyException;
use Lsxiao\JWT\Exception\TokenParseException;
use Lsxiao\JWT\Token;
use Symfony\Component\Yaml\Exception\ParseException;

class Parser
{

    /**
     * 解析Token
     * @param $token
     * @return Token
     * @throws SecretKeyException
     */
    public static function parseToken($token)
    {
        $components = self::splitToken($token);
        $header = self::parseHeader($components[0]);
        $payload = self::parsePayload($components[1]);
        $signature = self::parseSignature($components[2]);

        $secret = ConfigUtil::getSecretKey();
        $public = ConfigUtil::getPublicSecretKey();
        $private = ConfigUtil::getPrivateSecretKey();

        if ($header->isHMACAlgorithmId()) {
            if (empty($secret)) {
                throw new SecretKeyException('the sign algorithm is hmac,secret key not found in .env file');
            }
        } else if ($header->isRSAAlgorithmId()) {
            if (empty($public)) {
                throw new SecretKeyException('the sign algorithm is rsa,public key not found in .env file');
            }
            if (empty($private)) {
                throw new SecretKeyException('the sign algorithm is rsa,private key not found in .env file');
            }
        }

        return new Token($header, $payload, $secret, $private, $public, $signature);
    }

    /**
     * 分离Token结构
     * @param $token string
     * @return array
     * @throws TokenParseException
     */
    private static function splitToken($token)
    {
        $components = explode('.', $token);

        if (count($components) != 3) {
            throw new TokenParseException('token structure is wrong');
        };

        return $components;
    }

    /**
     * 解析头部
     * @param $data
     * @return Header
     * @throws TokenParseException
     */
    private static function parseHeader($data)
    {
        $headerJson = Base64Util::decode($data);

        if (!$headerJson) {
            throw new ParseException("JWT payload can not be base64 decode");
        }

        $header = json_decode($headerJson);

        if (!$header || !is_object($header)) {
            throw new ParseException("JWT payload can not be json decode");
        }

        if (!property_exists($header, 'typ')) {
            throw new TokenParseException('JWT header has no type');
        }

        if ($header->typ != 'JWT') {
            throw new TokenParseException('JWT header type is not JWT');
        }

        if (!property_exists($header, 'alg')) {
            throw new TokenParseException('JWT header has no algorithm');
        }

        return new Header($header->alg, $header->typ);
    }

    /**
     * 解析 Payload
     * @param $data
     * @return Payload
     * @throws TokenParseException
     */
    private static function parsePayload($data)
    {
        $claimsJson = Base64Util::decode($data);

        if (!$claimsJson) {
            throw new ParseException("JWT payload can not be base64 decode");
        }

        $claims = json_decode($claimsJson);

        if (!$claims || !is_object($claims)) {
            throw new TokenParseException('JWT payload can not be json decode');
        }

        $payload = new Payload();

        $only = ['iss', 'iat', 'exp', 'rexp', 'jti', 'nbf', 'sub', 'blgt'];

        if (count(get_object_vars($claims)) != count($only)) {
            throw new TokenParseException("JWT payload claim count is incorrect");
        }

        foreach ($only as $name) {
            if (!property_exists($claims, $name)) {
                throw new TokenParseException("JWT payload has no $name claim");
            }
        }

        //注意,解析后claim的顺序一定不能改变
        foreach ($claims as $name => $value) {
            $claim = ClaimFactory::create($name, $claims->{$name});
            $payload->addClaim($claim);
        }

        return $payload;
    }

    /**
     * 解析 signature
     * @param $data
     * @return Signature
     * @throws TokenParseException
     */
    private static function parseSignature($data)
    {
        if (empty($data)) {
            throw new TokenParseException('JWT signature is null or empty');
        }
        $value = Base64Util::decode($data);
        if (!$value) {
            throw new ParseException("JWT signature can not be decode");
        }
        return new Signature($value);
    }
}