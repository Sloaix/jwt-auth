<?php

use Lsxiao\JWT\Singer\HMAC;
use Lsxiao\JWT\Singer\RSA;
use PHPUnit\Framework\TestCase;

class SingerTestCase extends TestCase
{
    public function testHMACSingerAttribute()
    {
        $singer = new HMAC('HS256');
        $this->assertAttributeEquals('HS256', 'algorithmId', $singer);

        $singer = new HMAC('HS384');
        $this->assertAttributeEquals('HS384', 'algorithmId', $singer);

        $singer = new HMAC('HS512');
        $this->assertAttributeEquals('HS512', 'algorithmId', $singer);

        $this->assertEquals('HS256', HMAC::DEFAULT_ALGO_ID);

        $this->assertEquals([
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        ], HMAC::$ALGORITHM_MAP);
    }

    public function testRSASingerAttribute()
    {
        $singer = new RSA('RS256');
        $this->assertAttributeEquals('RS256', 'algorithmId', $singer);

        $singer = new RSA('RS384');
        $this->assertAttributeEquals('RS384', 'algorithmId', $singer);

        $singer = new RSA('RS384');
        $this->assertAttributeEquals('RS384', 'algorithmId', $singer);

        $this->assertEquals('RS256', RSA::DEFAULT_ALGO_ID);

        $this->assertEquals([
            'RS256' => OPENSSL_ALGO_SHA256,
            'RS384' => OPENSSL_ALGO_SHA384,
            'RS512' => OPENSSL_ALGO_SHA512,
        ], RSA::$ALGORITHM_MAP);
    }

    public function testHMACSingerReturn()
    {
        $singer = new HMAC('HS256');
        $this->assertEquals('sha256', $singer->getAlgorithm());
    }

    public function testRSASingerReturn()
    {
        $singer = new RSA('RS256');
        $this->assertEquals(OPENSSL_ALGO_SHA256, $singer->getAlgorithm());
    }

    public function testHMACSign256()
    {
        $hash = hash_hmac('sha256', 'data', 'secretKey');

        $singer = new HMAC('HS256');
        $this->assertEquals($hash, $singer->sign('data', 'secretKey'));
    }

    public function testHMACSign384()
    {
        $hash = hash_hmac('sha384', 'data', 'secretKey');

        $singer = new HMAC('HS384');
        $this->assertEquals($hash, $singer->sign('data', 'secretKey'));
    }

    public function testHMACSign512()
    {
        $hash = hash_hmac('sha512', 'data', 'secretKey');

        $singer = new HMAC('HS512');
        $this->assertEquals($hash, $singer->sign('data', 'secretKey'));
    }

    public function testHMACVerify256()
    {
        $expected = hash_hmac('sha256', 'data', 'secretKey');
        $singer = new HMAC('HS256');
        $this->assertTrue($singer->verify($expected, 'data', 'secretKey'));
    }

    public function testHMACVerify384()
    {
        $expected = hash_hmac('sha384', 'data', 'secretKey');
        $singer = new HMAC('HS384');
        $this->assertTrue($singer->verify($expected, 'data', 'secretKey'));
    }


    public function testHMACVerify512()
    {
        $expected = hash_hmac('sha512', 'data', 'secretKey');
        $singer = new HMAC('HS512');
        $this->assertTrue($singer->verify($expected, 'data', 'secretKey'));
    }


    public function testRSASign256()
    {
        $data = 'data';
        $privateKeyRes = openssl_pkey_new();
        openssl_pkey_export($privateKeyRes, $privateKey);

        openssl_sign($data, $signature, $privateKeyRes, OPENSSL_ALGO_SHA256);

        $singer = new RSA('RS256');
        $this->assertEquals($signature, $singer->sign($data, $privateKey));
    }


    public function testRSASign384()
    {
        $data = 'data';
        $privateKeyRes = openssl_pkey_new();
        openssl_pkey_export($privateKeyRes, $privateKey);

        openssl_sign($data, $signature, $privateKeyRes, OPENSSL_ALGO_SHA384);

        $singer = new RSA('RS384');
        $this->assertEquals($signature, $singer->sign($data, $privateKey));
    }

    public function testRSASign512()
    {
        $data = 'data';
        $privateKeyRes = openssl_pkey_new();
        openssl_pkey_export($privateKeyRes, $privateKey);

        openssl_sign($data, $signature, $privateKeyRes, OPENSSL_ALGO_SHA512);

        $singer = new RSA('RS512');
        $this->assertEquals($signature, $singer->sign($data, $privateKey));
    }


    public function testRSAVerify256()
    {
        $data = 'data';

        $privateKeyRes = openssl_pkey_new();
        openssl_pkey_export($privateKeyRes, $privateKey);
        $details = openssl_pkey_get_details($privateKeyRes);
        $publicKeyRes = openssl_pkey_get_public($details['key']);

        $singer = new RSA('RS256');
        $signature = $singer->sign($data, $privateKey);

        $this->assertTrue($singer->verify($signature, $data, $publicKeyRes));
    }

    public function testRSAVerify384()
    {
        $data = 'data';

        $privateKeyRes = openssl_pkey_new();
        openssl_pkey_export($privateKeyRes, $privateKey);
        $details = openssl_pkey_get_details($privateKeyRes);
        $publicKeyRes = openssl_pkey_get_public($details['key']);

        $singer = new RSA('RS384');
        $signature = $singer->sign($data, $privateKey);
        $this->assertTrue($singer->verify($signature, $data, $publicKeyRes));
    }


    public function testRSAVerify512()
    {
        $data = 'data';

        $privateKeyRes = openssl_pkey_new();
        openssl_pkey_export($privateKeyRes, $privateKey);
        $details = openssl_pkey_get_details($privateKeyRes);
        $publicKeyRes = openssl_pkey_get_public($details['key']);

        $singer = new RSA('RS512');
        $signature = $singer->sign($data, $privateKey);

        $this->assertTrue($singer->verify($signature, $data, $publicKeyRes));
    }


}
