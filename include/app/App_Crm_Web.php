<?php

/**
 * CRM页面基类
 */
class App_Crm_Web extends Base_App
{
    protected $lgmode;        //页面逻辑模式 -- pub (公开页面,不需登录); pri (私有页面,需要登录)
    protected $smarty;        //smarty 工具对象
    protected $_cid;        //客户id
    protected $_customer;   //客户id

    function __construct($lgmode = "pri")
    {
        $this->lgmode = $lgmode;
        $this->smarty = new Tool_Smarty(CRM_TEMPLATE_PATH);
        Tool_CssJs::setCssJsHost(CRM_HOST);
    }

    protected function checkAuth()
    {
        $this->_uid = $this->getLoginUid();
        if (empty($this->_uid))
        {
            echo 'Need Login~';
            exit;
        }
        $suserInfo = Admin_Api::getStaff($this->_uid);
        if (!Admin_Role_Api::hasRole($suserInfo, Conf_Admin::ROLE_SALES_NEW))
        {
            echo 'You are not a saler!';
            exit;
        }
    }

    protected function getLoginUid()
    {
        $verify = $_REQUEST['token'];
        $uid = Admin_Auth_Api::checkVerify($verify, Conf_Base::WEB_TOKEN_EXPIRED);
        if ($uid !== FALSE)
        {
            return $uid;
        }

        return FALSE;
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
}
