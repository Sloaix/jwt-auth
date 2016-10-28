<?php

use Lsxiao\JWT\Component\Claim\Audience;
use Lsxiao\JWT\Component\Claim\CustomClaim;
use Lsxiao\JWT\Component\Claim\ExpirationTime;
use Lsxiao\JWT\Component\Claim\IssuedAt;
use Lsxiao\JWT\Component\Claim\Issuer;
use Lsxiao\JWT\Component\Claim\JWTID;
use Lsxiao\JWT\Component\Claim\NotBefore;
use Lsxiao\JWT\Component\Claim\Subject;
use PHPUnit\Framework\TestCase;

class ClaimTestCase extends TestCase
{

    public function testCustomAttribute()
    {
        $claim = new CustomClaim('test', 1);
        $this->assertAttributeEquals('test', 'name', $claim);
        $this->assertAttributeEquals(1, 'value', $claim);
    }

    public function testCustomAttributeReturn()
    {
        $claim = new CustomClaim('test', 1);
        $this->assertEquals('test', $claim->getName());
        $this->assertEquals(1, $claim->getValue());
        $this->assertEquals('test', $claim->__toString());
    }

    public function testAudAttribute()
    {
        $str = "faith.epiphone@qq.com";
        $claim = new Audience($str);
        $this->assertAttributeEquals('aud', 'name', $claim);
        $this->assertAttributeEquals($str, 'value', $claim);
    }

    public function testAudAttributeReturn()
    {
        $str = "faith.epiphone@qq.com";
        $claim = new Audience($str);
        $this->assertEquals('aud', $claim->getName());
        $this->assertEquals($str, $claim->getValue());
        $this->assertEquals('aud', $claim->__toString());
    }

    public function testExpAttribute()
    {
        $time = time();
        $claim = new ExpirationTime($time);
        $this->assertAttributeEquals('exp', 'name', $claim);
        $this->assertAttributeEquals($time, 'value', $claim);
    }

    public function testExpReturn()
    {
        $time = time();
        $claim = new ExpirationTime($time);
        $this->assertEquals('exp', $claim->getName());
        $this->assertEquals($time, $claim->getValue());
        $this->assertEquals('exp', $claim->__toString());
    }


    public function testIatAttribute()
    {
        $time = time();
        $claim = new IssuedAt($time);
        $this->assertAttributeEquals('iat', 'name', $claim);
        $this->assertAttributeEquals($time, 'value', $claim);
    }

    public function testIatAttributeReturn()
    {
        $time = time();
        $claim = new IssuedAt($time);
        $this->assertEquals('iat', $claim->getName());
        $this->assertEquals($time, $claim->getValue());
        $this->assertEquals('iat', $claim->__toString());
    }


    public function testIssAttribute()
    {
        $str = "lsxiao";
        $claim = new Issuer($str);
        $this->assertAttributeEquals('iss', 'name', $claim);
        $this->assertAttributeEquals($str, 'value', $claim);
    }

    public function testIssAttributeReturn()
    {
        $str = "lsxiao";
        $claim = new Issuer($str);
        $this->assertEquals('iss', $claim->getName());
        $this->assertEquals($str, $claim->getValue());
        $this->assertEquals('iss', $claim->__toString());
    }

    public function testJtiAttribute()
    {
        $str = "123456789";
        $claim = new JWTID($str);
        $this->assertAttributeEquals('jti', 'name', $claim);
        $this->assertAttributeEquals($str, 'value', $claim);
    }

    public function testJtiAttributeReturn()
    {
        $str = "123456789";
        $claim = new JWTID($str);
        $this->assertEquals('jti', $claim->getName());
        $this->assertEquals($str, $claim->getValue());
        $this->assertEquals('jti', $claim->__toString());
    }

    public function testNbfAttribute()
    {
        $time = time();
        $claim = new NotBefore($time);
        $this->assertAttributeEquals('nbf', 'name', $claim);
        $this->assertAttributeEquals($time, 'value', $claim);
    }

    public function testNbfAttributeReturn()
    {
        $time = time();
        $claim = new NotBefore($time);
        $this->assertEquals('nbf', $claim->getName());
        $this->assertEquals($time, $claim->getValue());
        $this->assertEquals('nbf', $claim->__toString());
    }


    public function testSubAttribute()
    {
        $str = "lsxiao";
        $claim = new Subject($str);
        $this->assertAttributeEquals('sub', 'name', $claim);
        $this->assertAttributeEquals($str, 'value', $claim);
    }

    public function testSubAttributeReturn()
    {
        $str = "lsxiao";
        $claim = new Subject($str);
        $this->assertEquals('sub', $claim->getName());
        $this->assertEquals($str, $claim->getValue());
        $this->assertEquals('sub', $claim->__toString());
    }


    public function testRemoveDuplicate()
    {
        $array = [];
        $claim = new CustomClaim('test', 1);
        array_push($array, $claim);
        $claim = new CustomClaim('test', 1);
        array_push($array, $claim);
        $claim = new CustomClaim('test', 6);
        array_push($array, $claim);
        $claim = new Issuer('lsxiao');
        array_push($array, $claim);
        $this->assertEquals(2, count(array_unique($array)));
    }

}
