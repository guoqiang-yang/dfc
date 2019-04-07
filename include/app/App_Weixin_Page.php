<?php

/**
 * 微信网页基类-添加了一个参数：_openid
 */

class App_Weixin_Page extends App_Page
{

	protected $_openid;

	function __construct($lgmode)
	{
		parent::__construct($lgmode);

	}

	protected function checkAuth()
	{
		parent::checkAuth();

		if (!empty($_REQUEST['code']))
		{
			$this->_openid = WeiXin_Api::getOpenIdByCode($_REQUEST['code']);

			if (!empty($this->_openid) && $this->_cid > 0)
			{
				WeiXin_Api::saveOpenId($this->_cid, $this->_openid);
			}

			session_start();
			$_SESSION['openid'] = $this->_openid;

			//加个日志
			//Tool_Log::addFileLog('wx_get_openid_' . date('Ymd'), "{$this->_cid}\t{$this->_openid}");
		}
		else
		{
			session_start();

			if (!empty($_SESSION['openid']) && $this->_cid > 0)
			{
				WeiXin_Api::saveOpenId($this->_cid, $_SESSION['openid']);

				$this->_openid = $_SESSION['openid'];
			}
			else if (empty($_REQUEST['status']) && empty($_REQUEST['code']) && WeiXin_Api::isWx())
			{
				$http = $_SERVER['HTTPS'] == 'on' ? 'https:://' : 'http://';
				$referer =  $http . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];

				WeiXin_Api::auth($referer);
			}
		}

        //城市
        City_Api::setCity($_COOKIE['shop_city_id']);
	}
}