<?php

use Cjtaylor\Xmpp\AuthTypes\Plain;
use Cjtaylor\Xmpp\Options;
use PHPUnit\Framework\TestCase;

class AuthPlainTest extends TestCase
{
    /**
     * @var $plainAuth Plain
     */
    public $plainAuth;
    /**
     * @var $optionsStub Options
     */
    public $optionsStub;

    protected function setUp(): void
    {
        $this->optionsStub = $this->createMock(Options::class);
        $this->optionsStub->method('getUsername')->willReturn('Foo');
        $this->optionsStub->method('getPassword')->willReturn('Bar');

        $this->plainAuth = new Plain($this->optionsStub);
    }

    public function testIfCredentialsAreEncodedRight()
    {
        $this->assertEquals("AEZvbwBCYXI=", $this->plainAuth->encodedCredentials());
    }
}
