<?php

/**
 * 司机端-微信
 */

class App_Coopworker_Weixin extends App_Coopworker_H5
{
	protected $_openid;

	function __construct($lgmode)
	{
		if (date("Y-m-d H:i:s") <= "2016-12-14 22:00:00")
		{
			parent::__construct($lgmode);
		}
		else
		{
			header('Location: /index.php');
			exit;
		}
	}

	protected function checkAuth()
	{
		parent::checkAuth();

		if (BASE_HOST == '.haocaisong.cn')
		{
			if (!empty($_REQUEST['code']))
			{
				$this->_openid = WeiXin_Coopworker_Api::getOpenIdByCode($_REQUEST['code']);

				if (!empty($this->_openid) && $this->_uid > 0)
				{
					$type = !empty($this->_user['did']) ? Conf_Base::COOPWORKER_DRIVER : Conf_Base::COOPWORKER_CARRIER;
					WeiXin_Coopworker_Api::saveCoopworkerOpenid($this->_uid, $type, $this->_openid);
				}

				session_start();
				$_SESSION['openid'] = $this->_openid;
			}
			else
			{
				session_start();

				if (!empty($_SESSION['openid']) && $this->_uid > 0)
				{
					$type = !empty($this->_user['did']) ? Conf_Base::COOPWORKER_DRIVER : Conf_Base::COOPWORKER_CARRIER;
					WeiXin_Coopworker_Api::saveCoopworkerOpenid($this->_uid, $type, $_SESSION['openid']);

					$this->_openid = $_SESSION['openid'];
				}
				else if (empty($_REQUEST['status']) && empty($_REQUEST['code']) && WeiXin_Api::isWx())
				{
					$http = $_SERVER['HTTPS'] == 'on' ? 'https:://' : 'http://';
					$referer =  $http . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];

					WeiXin_Coopworker_Api::auth($referer);
				}
			}
		}
	}
}
