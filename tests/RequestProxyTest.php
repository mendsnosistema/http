<?php namespace Tests\HTTP;

use PHPUnit\Framework\TestCase;

final class RequestProxyTest extends TestCase
{
	protected RequestProxyMock $proxyRequest;

	public function setUp() : void
	{
		$this->proxyRequest = new RequestProxyMock([
			'real-domain.tld:8080',
		]);
	}

	public function testHost() : void
	{
		self::assertSame('real-domain.tld', $this->proxyRequest->getHost());
	}

	public function testAccept() : void
	{
		self::assertSame([], $this->proxyRequest->getAccepts());
	}

	public function testIsAJAX() : void
	{
		self::assertFalse($this->proxyRequest->isAJAX());
		self::assertFalse($this->proxyRequest->isAJAX());
	}

	public function testIsSecure() : void
	{
		self::assertTrue($this->proxyRequest->isSecure());
		self::assertTrue($this->proxyRequest->isSecure());
	}

	public function testJSON() : void
	{
		$this->proxyRequest->setBody('{"test":123}');
		self::assertSame(123, $this->proxyRequest->getJSON()->test);
	}

	public function testPort() : void
	{
		self::assertSame(8080, $this->proxyRequest->getPort());
	}

	public function testProxiedIP() : void
	{
		self::assertSame('192.168.1.2', $this->proxyRequest->getProxiedIP());
	}

	public function testReferer() : void
	{
		self::assertNull($this->proxyRequest->getReferer());
	}

	public function testURL() : void
	{
		self::assertSame(
			'https://real-domain.tld:8080/blog/posts?order_by=title&order=asc',
			(string) $this->proxyRequest->getURL()
		);
		self::assertInstanceOf(\Framework\HTTP\URL::class, $this->proxyRequest->getURL());
	}
}
