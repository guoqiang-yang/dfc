<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/11/30
 * Time: 15:49
 */
class Sms_Yixinxi implements Sms_Interface
{
    private $_msg;
    private $_mobile;
    private $_isVerifyCode;

    private static $_NAME = '18811412450';
    private static $_PASSWORD = 'B7CCD8ECBE871E2680C1492AF324';
    private static $_SIGN_NAME = '好材';
    private static $_TYPE = 'pt';
    private static $_NAME_SUB = '好材短信子账号';
    private static $_PASSWORD_SUB = 'B7CCD8ECBE871E2680C1492AF324';

    public function __construct($mobile, $key, $para)
    {
        $this->_mobile = $mobile;
        $this->_msg = Conf_Sms::getMessage($key, $para);

        $this->_isVerifyCode = Conf_Sms::isVerifyMessage($key);
    }

    public function send()
    {
        if ($this->_isVerifyCode)
        {
            $params = array(
                'name' => self::$_NAME,
                'pwd' => self::$_PASSWORD,
                'mobile' => $this->_mobile,
                'content' => $this->_msg,
                'sign' => self::$_SIGN_NAME,
                'type' => self::$_TYPE,
            );
        }
        else
        {
            $params = array(
                'name' => self::$_NAME_SUB,
                'pwd' => self::$_PASSWORD_SUB,
                'mobile' => $this->_mobile,
                'content' => $this->_msg . ' 回T退订',
                'sign' => self::$_SIGN_NAME,
                'type' => self::$_TYPE,
            );
        }

        return Tool_Http::post('http://web.1xinxi.cn/asmx/smsservice.aspx', $params);
    }
}