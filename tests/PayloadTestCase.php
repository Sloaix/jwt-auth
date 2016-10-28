<?php
use Lsxiao\JWT\Component\Claim\CustomClaim;
use Lsxiao\JWT\Component\Claim\Issuer;
use Lsxiao\JWT\Component\Claim\Subject;
use Lsxiao\JWT\Component\Payload;
use Lsxiao\JWT\Util\Base64Util;
use PHPUnit\Framework\TestCase;

class PayloadTestCase extends TestCase
{

    public function testAttribute()
    {
        $payload = new Payload();
        $this->assertAttributeEquals([], 'claims', $payload);
    }

    public function testDuplicate()
    {
        $payload = new Payload();

        $payload->addClaim(new CustomClaim('test', 'test'));
        $payload->addClaim(new CustomClaim('test', 'test'));
        $this->assertEquals(1, count($payload->getClaims()));

        $payload->addClaim(new Issuer('lsxiao'));
        $this->assertEquals(2, count($payload->getClaims()));

        $payload->addClaim(new Issuer('lsxiao'));
        $this->assertEquals(2, count($payload->getClaims()));
    }

    public function testOverride()
    {
        $payload = new Payload();

        $payload->addClaim(new CustomClaim('test', 'test'));
        $this->assertEquals("test", $payload->getClaims()[0]->getName());

        $payload->addClaim(new CustomClaim('test', 'override'));
        $this->assertEquals("override", $payload->getClaims()[0]->getValue());
    }


    public function testToJsonString()
    {
        $array = [new Issuer('lsxiao'), new Subject('poem')];
        $payload = new Payload($array);
        $this->assertEquals('{"iss":"lsxiao","sub":"poem"}', $payload->toJsonString());
    }

    public function testToBase64()
    {
        $array = [new Issuer('lsxiao'), new Subject('poem')];
        $payload = new Payload($array);
        $this->assertEquals(Base64Util::encode('{"iss":"lsxiao","sub":"poem"}'), $payload->toBase64String());
    }
}
