<?php

namespace Berry;

class HTTP
{
	private $request;
	private $xmlrequest;
	private $method;

	function __construct()
	{
		$this->request 		= (bool) $this->serverArray('HTTP_REFERER');
		$this->xmlrequest 	= $this->serverArray('HTTP_X_REQUESTED_WITH');
		$this->method 		= $this->serverArray('REQUEST_METHOD');
	}

	public function isPost()
	{
		return $this->request && $this->method == 'POST' ?
			true : false;
	}

	public function isQuery()
	{
		return $this->request && $this->method == 'GET' ?
			true : false;
	}

	public function isRequest()
	{
		return $this->request ?
			true : false;
	}

	public function isAjax()
	{
		if(!empty($this->xmlrequest)
			&& strtolower($this->xmlrequest) == 'xmlhttprequest') {
			return true;
		}

		return false;
	}

	public function getPost()
	{
		return $_POST;
	}

	public function getQuery()
	{
		return $_GET;
	}

	public function getRequest()
	{
		return $_REQUEST;
	}

	public function serverArray($attr)
	{
		return isset($_SERVER[$attr]) ?
			$_SERVER[$attr] : null;
	}
}
