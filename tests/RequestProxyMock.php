<?php namespace Tests\HTTP;

class RequestProxyMock extends RequestMock
{
	protected function prepareServerVariables()
	{
		$this->serverVariables = [
			'HTTP_HOST' => 'real-domain.tld:8080',
			'HTTP_X_FORWARDED_FOR' => '192.168.1.2',
			'HTTP_REFERER' => 'invali_d',
			'REMOTE_ADDR' => '192.168.1.100',
			'REQUEST_METHOD' => 'GET',
			'REQUEST_SCHEME' => 'http',
			'HTTPS' => 'on',
			'REQUEST_URI' => '/blog/posts?order_by=title&order=asc',
			'SERVER_PORT' => 8080,
			'SERVER_PROTOCOL' => 'HTTP/1.1',
			'SERVER_NAME' => 'domain.tld',
		];
	}

	protected function prepareBody()
	{
		$this->body = '{"test":123}';
	}

	protected function prepareCookies()
	{
		parent::prepareCookies();
		$this->removeCookie('X-CSRF-Token');
	}
}
