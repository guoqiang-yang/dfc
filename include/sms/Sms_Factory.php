<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/11/30
 * Time: 16:22
 */
class Sms_Factory
{
    public static function getSmsEngine($type, $mobile, $key, $para)
    {
        switch ($type)
        {
            case 'aliyun':
                return new Sms_Aliyun($mobile, $key, $para);
                break;
            case 'yixinxi':
                return new Sms_Yixinxi($mobile, $key, $para);
                break;
            default:
                throw new Exception('无效的邮件引擎！');
        }
    }
}