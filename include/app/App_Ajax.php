<?php
/**
 * ajax后台程序基类
 */

class App_Ajax extends App_Web
{
	protected $allowedMethods = array('HEAD', 'GET', 'POST');

	protected function checkAuth()
	{
		if (!in_array($_SERVER["REQUEST_METHOD"], $this->allowedMethods))
		{
			header("HTTP/1.1 405 Method Not Allowed");
			header("Allow: ".implode($this->allowedMethods, ", "));
			exit;
		}

		//城市
		//City_Api::setCity($_COOKIE['shop_city_id']);
        
        //城市
        $city = isset($_COOKIE['shop_city_id'])&&!empty($_COOKIE['shop_city_id'])?$_COOKIE['shop_city_id']:     //h5
                (isset($_COOKIE['city_id'])&&!empty($_COOKIE['city_id']) ? $_COOKIE['city_id']: Conf_City::BEIJING); //app
        City_Api::setCity($city);

		parent::checkAuth();

		if ($this->lgmode == 'pri' && (!$this->_uid || !$this->_cid))
		{
			$cookie = var_export($_COOKIE, true);
			$session = var_export($_SESSION, true);
			$str = "cid={$this->_cid}; uid={$this->_uid}; cookie=$cookie; session=$session";
			Tool_Log::addFileLog('client/ajax_param_error_'.date('Ymd'), $str);
		}
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
		if ($ex->getMessage() == "common:fast operate")
		{
			$this->goToErrorFast();
		}
		else
		{
			$rawmsg = $ex->getMessage();
			if (isset(Conf_Exception::$exceptions[$rawmsg]))
			{
				list($errno,$errmsg) = Conf_Exception::$exceptions[$rawmsg];
			}
			else
			{
				$errno = $ex->getCode();
				$errmsg = $rawmsg;//Conf_Exception::DEFAULT_ERRMSG;
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

	public function goToErrorFast()
	{
		$response = new Response_Ajax();
		$response->seeOther("/common/error_fast.php");
		$response->send();
		exit;
	}
}