<?php namespace Tests\HTTP;

use Framework\HTTP\Response;
use PHPUnit\Framework\TestCase;

final class ResponseDownloadTest extends TestCase
{
	protected Response $response;
	protected RequestMock $request;

	protected function setUp() : void
	{
		$this->request = new RequestMock([
			'domain.tld',
		]);
		$this->request->setHeader('Range', 'bytes=0-499');
		$this->response = new class($this->request) extends Response {
		};
	}

	public function testInvalidFilepath() : void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->response->setDownload(__DIR__ . '/unknown');
	}

	public function testInvalidRequestHeader() : void
	{
		$this->request->setHeader('Range', 'xx');
		$this->response->setDownload(__FILE__);
		self::assertSame(416, $this->response->getStatusCode());
		self::assertStringStartsWith('*/', $this->response->getHeader('Content-Range'));
	}

	public function testSingleByteRanges() : void
	{
		$this->response->setDownload(__FILE__);
		self::assertSame(206, $this->response->getStatusCode());
		self::assertSame('text/x-php', $this->response->getHeader('Content-Type'));
		self::assertSame('500', $this->response->getHeader('Content-Length'));
		self::assertStringStartsWith('bytes 0-499/', $this->response->getHeader('Content-Range'));
	}

	public function testMultiByteRanges() : void
	{
		$this->request->setHeader('Range', 'bytes=0-499,500-549');
		$this->response->setDownload(__FILE__);
		self::assertSame(206, $this->response->getStatusCode());
		self::assertStringStartsWith(
			'multipart/x-byteranges; boundary=',
			$this->response->getHeader('Content-Type')
		);
		self::assertSame('822', $this->response->getHeader('Content-Length'));
	}

	public function testInvalidHeaderValue() : void
	{
		$this->request->setHeader('Range', 'x');
		$this->response->setDownload(__FILE__);
		self::assertSame(416, $this->response->getStatusCode());
	}

	public function testInvalidRange() : void
	{
		$this->request->setHeader('Range', 'bytes=');
		$this->response->setDownload(__FILE__);
		self::assertSame(416, $this->response->getStatusCode());
		$this->request->setHeader('Range', 'bytes=-');
		$this->response->setDownload(__FILE__);
		self::assertSame(416, $this->response->getStatusCode());
		$this->request->setHeader('Range', 'bytes=a-');
		$this->response->setDownload(__FILE__);
		self::assertSame(416, $this->response->getStatusCode());
		$this->request->setHeader('Range', 'bytes=0-y');
		$this->response->setDownload(__FILE__);
		self::assertSame(416, $this->response->getStatusCode());
		$this->request->setHeader('Range', 'bytes=0-10000');
		$this->response->setDownload(__FILE__);
		self::assertSame(416, $this->response->getStatusCode());
	}

	public function testRange() : void
	{
		$this->request->setHeader('Range', 'bytes=0-10');
		$this->response->setDownload(__FILE__);
		self::assertSame(206, $this->response->getStatusCode());
		$this->request->setHeader('Range', 'bytes=0-10,11-19,25-,-98');
		$this->response->setDownload(__FILE__);
		self::assertSame(206, $this->response->getStatusCode());
	}

	public function testHasDownload() : void
	{
		self::assertFalse($this->response->hasDownload());
		$this->response->setDownload(__FILE__);
		self::assertTrue($this->response->hasDownload());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSendSinglePart() : void
	{
		$this->request->setHeader('Range', 'bytes=0-9');
		$this->response->setDownload(__FILE__);
		\ob_start();
		$this->response->send();
		\ob_end_clean();
		self::assertTrue($this->response->isSent());
		self::assertStringStartsWith('bytes 0-9/', $this->response->getHeader('Content-Range'));
		self::assertStringEqualsFile(__FILE__, $this->response->getBody());
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testSendMultiPart() : void
	{
		$this->request->setHeader('Range', 'bytes=0-9,10-19');
		$this->response->setDownload(__FILE__);
		\ob_start();
		$this->response->send();
		\ob_end_clean();
		self::assertTrue($this->response->isSent());
		self::assertStringStartsWith(
			'multipart/x-byteranges; boundary=',
			$this->response->getHeader('Content-Type')
		);
	}

	/**
	 * @runInSeparateProcess
	 */
	public function testReadFile() : void
	{
		$this->response->setDownload(__FILE__, false, false);
		\ob_start();
		$this->response->send();
		\ob_end_clean();
		self::assertTrue($this->response->isSent());
	}
}
