<?php

/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/6/1
 * Time: 13:59
 */
class App_Corp_Web extends Base_App
{
    protected $lgmode;    //页面逻辑模式 -- pub (公开页面,不需登录); pri (私有页面,需要登录)
    protected $smarty;    //smarty 工具对象
    protected $_cid;        //客户id
    protected $_uid;
    protected $_user;


    function __construct($lgmode = "pri", $templatePath = CORP_TEMPLATE_PATH)
    {
        $this->lgmode = $lgmode;
        $this->smarty = new Tool_Smarty($templatePath);
    }

    protected function checkAuth()
    {
        $this->_uid = $this->getLoginUid();
        if ($this->_uid)
        {
            $userInfo = Crm2_Api::getUserInfo($this->_uid);
            $this->_user = $userInfo['user'];
            $this->_cid = $userInfo['user']['cid'];
        }
    }

    protected function free()
    {
        Data_DB::free();
    }

    protected function setCommonPara()
    {
        $this->smarty->assign("_uid", $this->_uid);
        $this->smarty->assign("_cid", $this->_cid);
    }

    protected function showError($ex)
    {
        $error = "[" . $ex->getCode() . "]: " . $ex->getMessage();
        if ($ex->reason)
        {
            $error .= ' ' . $ex->reason;
        }
        echo $error . "\n";

        print_r($ex->getTrace());
        echo "\n";
    }

    protected function getLoginUid()
    {
        $verify = Tool_Input::clean('c', '_session', TYPE_STR);

        // 如果cookie::_session为空，从_REQUEST中去
        if (empty($verify))
        {
            $verify = $_REQUEST['token'];
        }

        $uid = Crm2_Auth_Api::checkVerify($verify, Conf_Base::WEB_TOKEN_EXPIRED);
        if ($uid !== FALSE)
        {
            return $uid;
        }

        if ($this->lgmode ==' pri')
        {
            header('Location: /user/login.php');
            self::clearVerifyCookie();
        }

        return false;
    }

    protected function goToErrorFast()
    {
        header('Location: /common/error_fast.php');
        exit;
    }

    protected function setSessionVerifyCookie($token, $expiredTime = 0)
    {
        $expiredTime = !empty($expiredTime) ? (time() + $expiredTime) : 0;
        setcookie("_session", $token, $expiredTime, "/", WEB_CORP_HOST);
        setcookie('_uid', $this->_uid, $expiredTime, '/', WEB_CORP_HOST);
    }

    protected function clearVerifyCookie()
    {
        setcookie('_session', '', -86400, '/', WEB_CORP_HOST);
        setcookie('_uid', '', -86400, '/', WEB_CORP_HOST);
        setcookie('_session', '', -86400, '/', Conf_Base::getBaseHost());
        setcookie('_uid', '', -86400, '/', Conf_Base::getBaseHost());
    }
}