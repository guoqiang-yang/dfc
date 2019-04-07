<?php

/**
 * 第三方工人管理 - Web基类
 */
class App_Coopworker_Web extends Base_App
{
	protected $lgmode;		//页面逻辑模式 -- pub (公开页面,不需登录); pri (私有页面,需要登录)
	protected $smarty;		//smarty 工具对象
    
    protected $_uid;       //合作工人的ID
    protected $_user;      //合作工人的个人信息
    protected $_userType;   //合作工人的身份

	function __construct($lgmode='pri', $tmplpath=ADMIN_TEMPLATE_PATH)
	{
		$this->lgmode = $lgmode;
		$this->smarty = new Tool_Smarty($tmplpath);

		$this->printLog();
	}

	private function printLog()
	{
		$info = sprintf("\n-------------client query param: %s--------------\n", $_SERVER['REQUEST_URI']);
		$info .= "request = " . var_export($_REQUEST, true) . "\n";
		$info .= "cookie = "  . var_export($_COOKIE, true). "\n";

		//Tool_Log::addFileLog('co/query_params_'.date('Ymd'), $info);
	}

	protected function checkAuth()
	{
		$this->getLoginUserInfo();
	}

	protected function setCommonPara()
	{
		$this->smarty->assign('_imgHost', ADMIN_IMG_HOST);
		$this->smarty->assign("_wwwHost", ADMIN_HOST);
		$this->smarty->assign("_uid", $this->_uid);
		$this->smarty->assign("_user", $this->_user);
  
	}

	protected function showError($ex)
	{
		$error = "[".$ex->getCode()."]: ".$ex->getMessage();
		if ($ex->reason)
		{
			$error .= ' '.$ex->reason;
		}
		echo $error."\n";

		print_r($ex->getTrace());
		echo "\n";
	}

	protected function getLoginUserInfo()
	{
		$verify = Tool_Input::clean('c', '_co_session', TYPE_STR);
        $user = Logistics_Auth_Api::checkVerify($verify, Conf_Base::WEB_TOKEN_EXPIRED);
        
        if (!empty($user) && isset($user['uid']) && !empty($user['uid']))
        {
            $this->_uid = $user['uid'];
            $this->_user = $user['user_info'];
            $this->_userType = $user['user_type'];
            
            return;
        }
        
		self::clearVerifyCookie();
		return false;
	}
    
	protected function setSessionVerifyCookie($token, $expiredTime=0)
	{
		$expiredTime = !empty($expiredTime) ? (time() + $expiredTime) : 0;
        
		setcookie("_co_session", $token, $expiredTime, "/", Conf_Base::getBaseHost());
		setcookie('_co_uid', $this->_uid, $expiredTime, '/', Conf_Base::getBaseHost());
	}

	protected static function clearVerifyCookie()
	{
		setcookie('_co_session', '', -86400, '/', Conf_Base::getBaseHost());
		setcookie('_co_uid', '', -86400, '/', Conf_Base::getBaseHost());
	}


}
