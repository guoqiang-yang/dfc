<?php
class App_Driver_Ajax extends App_DriverApi
{
	protected $allowedMethods = array('GET', 'POST');

	protected function checkAuth()
	{
		if (!in_array($_SERVER["REQUEST_METHOD"], $this->allowedMethods))
		{
			header("HTTP/1.1 405 Method Not Allowed");
			header("Allow: ".implode($this->allowedMethods, ", "));
			exit;
		}

		if ($_SERVER['SERVER_ADDR']!='127.0.0.1'
		    && empty($this->version) )
		{
			throw new Exception('common:params error');
		}

		if ($this->lgmode == 'pri')
		{
			$this->_checkUserInfo();
			$this->_setUserInfo();
		}
	}

	private function _checkUserInfo()
	{
		if (empty($this->token))
		{
			throw new Exception('common:to login');
		}

		$uid = Logistics_Auth_Api::checkVerifyApp($this->token, Conf_Base::WEB_TOKEN_EXPIRED);

		if($uid === false)
		{
			throw new Exception('common:to login');
		}


	}

	private function _setUserInfo()
	{
		$tokens = explode('_', $this->token);
		$this->uid = $tokens[2];
		$this->cid = $tokens[3];
	}

	protected function setAllowedMethod($method)
	{
		$this->allowedMethods = array_map("strtoupper", (array) $method);
	}

	protected function outputPage()
	{
		$this->setCommonPara();
		$this->outputBody();
	}

	protected function showError($ex)
	{
		$rawmsg = $ex->getMessage();
		if (isset(Conf_Exception::$exceptions[$rawmsg]))
		{
			list($errno,$errmsg) = Conf_Exception::$exceptions[$rawmsg];
		}
		else
		{
			$errno = $ex->getCode();
			$errmsg = $rawmsg;
		}

		$response = new Response_Ajax();
		$errorArr = array(
			"errmsg" => $errmsg,
			"errno" => $errno,
		);
		if (defined('DEBUG_MODE') && DEBUG_MODE)
		{
			$errorArr["trace"] = $rawmsg . "\n" . var_export($ex->getTrace(), true);
		}
		$response->setError($errorArr);
		$response->send();
		Tool_Log::debug('@app_ajax', "code:".$ex->getCode()."\nerror:" . $ex->getMessage() . "\n" . var_export($ex->getTrace(),true));
	}

}
