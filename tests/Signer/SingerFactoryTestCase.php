<?php

use Lsxiao\JWT\Singer\HMAC;
use Lsxiao\JWT\Singer\RSA;
use Lsxiao\JWT\Singer\SingerFactory;
use PHPUnit\Framework\TestCase;

class SingerFactoryTestCase extends TestCase
{

    public function testHMACSinger()
    {
        $singer = SingerFactory::createHMACSigner('HS256');
        $this->assertInstanceOf(HMAC::class, $singer);
    }

    public function testHMACSingerCreateByAlgoId()
    {
        $singer = SingerFactory::createByAlgorithmId('HS256');
        $this->assertInstanceOf(HMAC::class, $singer);
    }

    public function testHMACSingerAlgorithm()
    {
        $singer = SingerFactory::createHMACSigner('HS256');
        $this->assertEquals($singer->getAlgorithm(), 'sha256');

        $singer = SingerFactory::createHMACSigner('HS384');
        $this->assertEquals($singer->getAlgorithm(), 'sha384');

        $singer = SingerFactory::createHMACSigner('HS512');
        $this->assertEquals($singer->getAlgorithm(), 'sha512');
    }

    public function testRSASinger()
    {
        $singer = SingerFactory::createRsaSigner('RS256');
        $this->assertInstanceOf(RSA::class, $singer);
    }

    public function testRSASingerCreateByAlgoId()
    {
        $singer = SingerFactory::createByAlgorithmId('RS256');
        $this->assertInstanceOf(RSA::class, $singer);
    }

    public function testRSASingerAlgorithm()
    {
        $singer = SingerFactory::createRsaSigner('RS256');
        $this->assertEquals($singer->getAlgorithm(), OPENSSL_ALGO_SHA256);

        $singer = SingerFactory::createRsaSigner('RS384');
        $this->assertEquals($singer->getAlgorithm(), OPENSSL_ALGO_SHA384);

        $singer = SingerFactory::createRsaSigner('RS512');
        $this->assertEquals($singer->getAlgorithm(), OPENSSL_ALGO_SHA512);
    }


}
