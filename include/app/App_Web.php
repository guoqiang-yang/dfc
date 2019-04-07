<?php

/**
 * Web 页面基类
 */
class App_Web extends Base_App
{
	protected $lgmode;		//页面逻辑模式 -- pub (公开页面,不需登录); pri (私有页面,需要登录)
	protected $smarty;		//smarty 工具对象

	protected $_cid;        //客户id
	protected $_customer;   //客户id

	function __construct($lgmode = "pri")
	{
		$this->lgmode = $lgmode;
		$this->smarty = new Tool_Smarty(C_H5_TEMPLATE_PATH);
        
//        $this->printLog();
	}
    
    private function printLog()
	{
		$verify = Tool_Input::clean('c', '_session', TYPE_STR);
		$info = sprintf("\n-------------client query param: %s--------------\n", $_SERVER['REQUEST_URI']);
		$info .= "request = " . var_export($_REQUEST, true) . "\n";
        $info .= "cookie = "  . var_export($_COOKIE, true). "\n";
		$info .= "verify = " . $verify . "\n";
        //$info .= "server = "  . var_export($_SERVER, true). "\n";

		Tool_Log::addFileLog('client/query_params_'.date('Ymd'), $info);
	}

	protected function checkAuth()
	{
		$this->_uid = $this->getLoginUid();

		// 已登录状态处理
		if ($this->_uid)
		{
            $userInfo = Crm2_Api::getUserInfo($this->_uid);
            $this->_user = $userInfo['user'];
            $this->_customer = $userInfo['customer'];
            $this->_cid = $this->_user['cid'];
		}
        
        if (empty($this->_cid)||empty($this->_uid))
        {
            self::clearVerifyCookie();
        }
	}

	protected function free()
	{
		Data_DB::free();
	}

	protected function setCommonPara()
	{
		$this->smarty->assign('_imgHost', C_H5_IMG_HOST);
		$this->smarty->assign("_wwwHost", C_H5_WWW_HOST);
		$this->smarty->assign('_mainHost', C_H5_MAIN_HOST);
		$this->smarty->assign("_uid", $this->_uid);
		$this->smarty->assign("_user", $this->_user);
		$this->smarty->assign("_customer", $this->_customer);
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

	protected function getLoginUid()
	{
		$verify = $_REQUEST['token'];
        
        // 如果cookie::_session为空，从_REQUEST中去
        if(empty($verify))
        {
            $verify = Tool_Input::clean('c', '_session', TYPE_STR);
        }
        
//        //如果传了token-就解析token里头的uid
//        //有一些页面是需要在app里头调用的，app通过传token获得登录状态
//        if (!empty($_REQUEST['token']))
//        {
//            $verify = $_REQUEST['token'];   
//        }

		$uid = Crm2_Auth_Api::checkVerify($verify, Conf_Base::WEB_TOKEN_EXPIRED);

		if($uid !== false)
		{
			return $uid;
		}

        if ($this->lgmode =='pri')
        {
            if ( strtolower($_COOKIE['platform'])=='ios' || strtolower($_REQUEST['platform'])=='ios')
            {
                echo "<script>window.webkit.messageHandlers.logout.postMessage({});</script>";
                exit;
            }
            else if (strtolower($_COOKIE['platform'])=='android' || strtolower($_REQUEST['platform'])=='android')
            {
                echo "<script>product.logout();</script>";
                exit;
            }
            else
            {
                self::clearVerifyCookie();
                return false;
            }
        }
	}

	protected function delegateTo($path)
	{
		chdir(C_H5_HTDOCS_PATH."/".dirname($path));

		require_once C_H5_HTDOCS_PATH."/".$path;
	}

	protected function goToErrorFast()
	{
		header('Location: /common/error_fast.php');
		exit;
	}

	protected function gotoLoginRequired()
	{
	}

	protected function goToInvalidUser($state)
	{
		//header('Location: /common/user_state.php?state='.$state."&fuid=".$this->_uid);
		//exit;
	}

	protected function setSessionVerifyCookie($token, $expiredTime=0)
	{
		$expiredTime = !empty($expiredTime) ? (time() + $expiredTime) : 0;
		setcookie("_session", $token, $expiredTime, "/", Conf_Base::getBaseHost());
		setcookie('_uid', $this->_uid, $expiredTime, '/', Conf_Base::getBaseHost());

        setcookie("_session", $token, $expiredTime, "/", 'wangyichuan.cn');
        setcookie('_uid', $this->_uid, $expiredTime, '/', 'wangyichuan.cn');
	}

	protected function clearVerifyCookie()
	{
		setcookie('_session', '', -86400, '/', Conf_Base::getMainHost());
		setcookie('_uid', '', -86400, '/', Conf_Base::getMainHost());
		setcookie('_session', '', -86400, '/', Conf_Base::getBaseHost());
		setcookie('_uid', '', -86400, '/', Conf_Base::getBaseHost());

        setcookie('_session', '', -86400, '/', 'wangyichuan.cn');
        setcookie('_uid', '', -86400, '/', 'wangyichuan.cn');
	}

}
