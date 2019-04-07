<?php
class App_Coopworker_Ajax extends App_Coopworker_Web
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

		parent::checkAuth();

        //城市
        City_Api::setCity($_COOKIE['shop_city_id']);
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
