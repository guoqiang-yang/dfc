<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/6/1
 * Time: 13:59
 */

class App_Toc_Web extends Base_App
{
    protected $lgmode;	//页面逻辑模式 -- pub (公开页面,不需登录); pri (私有页面,需要登录)
    protected $smarty;	//smarty 工具对象

    protected $_cid;        //客户id
    protected $_customer;   //客户id

    function __construct($lgmode = "pri", $templatePath = TOC_H5_TEMPLATE_PATH)
    {
        $this->lgmode = $lgmode;
        $this->smarty = new Tool_Smarty($templatePath);
    }

    private function printLog()
    {
        $verify = Tool_Input::clean('c', '_session', TYPE_STR);
        $info = sprintf("\n-------------client query param: %s--------------\n", $_SERVER['REQUEST_URI']);
        $info .= "request = " . var_export($_REQUEST, true) . "\n";
        $info .= "cookie = "  . var_export($_COOKIE, true). "\n";
        $info .= "verify = " . $verify . "\n";
        //$info .= "server = "  . var_export($_SERVER, true). "\n";

        Tool_Log::addFileLog('client/toc_h5_request_'.date('Ymd'), $info);
    }

    protected function checkAuth()
    {
//        $this->_uid = $this->getLoginUid();
        // 已登录状态处理
        if ($this->_uid)
        {
            //TODO
        }
        else
        {
            //self::clearVerifyCookie();
        }
    }

    protected function free()
    {
        Data_DB::free();
    }

    protected function setCommonPara()
    {
        $this->smarty->assign("_wwwHost", TOC_H5_MAIN_HOST);
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

    protected function getLoginUid()
    {
        $verify = Tool_Input::clean('c', '_session', TYPE_STR);

        // 如果cookie::_session为空，从_REQUEST中去
        if(empty($verify))
        {
            $verify = $_REQUEST['token'];
        }

        $uid = Crm2_Auth_Api::checkVerify($verify, Conf_Base::WEB_TOKEN_EXPIRED);
        if($uid !== false)
        {
            return $uid;
        }

//        if ($this->lgmode ==' pri')
//        {
//            if ( strtolower($_COOKIE['platform'])=='ios' || strtolower($_REQUEST['platform'])=='ios')
//            {
//                echo "<script>window.webkit.messageHandlers.logout();</script>";
//                exit;
//            }
//            else if (strtolower($_COOKIE['platform'])=='android' || strtolower($_REQUEST['platform'])=='android')
//            {
//                echo "<script>product.logout();</script>";
//                exit;
//            }
//            else
//            {
//                self::clearVerifyCookie();
//                return false;
//            }
//        }
    }

    protected function delegateTo($path)
    {
        chdir(TOC_H5_HTDOCS_PATH . "/" . dirname($path));

        require_once TOC_H5_HTDOCS_PATH . "/" . $path;
    }

    protected function goToErrorFast()
    {
        header('Location: /common/error_fast.php');
        exit;
    }

    protected function setSessionVerifyCookie($token, $expiredTime=0)
    {
        $expiredTime = !empty($expiredTime) ? (time() + $expiredTime) : 0;
        setcookie("_session", $token, $expiredTime, "/", TOC_H5_MAIN_HOST);
        setcookie('_uid', $this->_uid, $expiredTime, '/', TOC_H5_MAIN_HOST);
    }

    protected function clearVerifyCookie()
    {
        setcookie('_session', '', -86400, '/', TOC_H5_MAIN_HOST);
        setcookie('_uid', '', -86400, '/', TOC_H5_MAIN_HOST);
    }

}