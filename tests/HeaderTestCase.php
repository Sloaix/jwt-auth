<?php

use Lsxiao\JWT\Component\Header;
use PHPUnit\Framework\TestCase;

class HeaderTestCase extends TestCase
{

    public function testAttribute()
    {
        $header = new Header();

        $this->assertAttributeEquals('JWT', 'type', $header);
        $this->assertAttributeEquals('HS256', 'algorithmId', $header);
    }

    public function testToJsonString()
    {
        $header = new Header();
        $expects = json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]);
        $this->assertEquals($expects, $header->toJsonString());
    }

    public function testToBase64String()
    {
        $header = new Header();
        $expects = base64_encode(json_encode([
            'typ' => 'JWT',
            'alg' => 'HS256'
        ]));
        $this->assertEquals($expects, $header->toBase64String());
    }

    public function testGetAlgorithmId()
    {
        $header = new Header();
        $this->assertEquals('HS256', $header->getAlgorithmId());
    }

    public function testSetAlgorithmId()
    {
        $header = new Header();
        $header->setAlgorithmId('HS512');
        $this->assertEquals('HS512', $header->getAlgorithmId());
    }
}
