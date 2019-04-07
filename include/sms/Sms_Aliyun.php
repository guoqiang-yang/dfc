<?php

/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/11/30
 * Time: 15:39
 */
class Sms_Aliyun implements Sms_Interface
{
    private $_templateId;
    private $_para;
    private $_mobile;

    private static $_ACCESSKEY_ID = 'LTAI8xLoqhLezMge';
    private static $_ACCESSKEY_SECRET = '8vtppMJTdCChDwsZPiTD5fTaYnLFPm';
    private static $_SIGN_NAME = '好材';


    public function __construct($mobile, $key, $para)
    {
        $this->_mobile = $mobile;
        $this->_templateId = Conf_Sms::getAliyunTemplateId($key);
        $this->_para = $para;
    }

    public function send()
    {
        date_default_timezone_set('GMT');

        $para = array(
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => time(),
            'AccessKeyId' => self::$_ACCESSKEY_ID,
            'SignatureVersion' => '1.0',
            'Timestamp' => date('Y-m-d') . "T" . date('H:i:s') . "Z",
            'Format' => 'JSON',
            'Action' => 'SendSms',
            'Version' => '2017-05-25',
            'RegionId' => 'cn-hangzhou',
            'PhoneNumbers' => $this->_mobile,
            'SignName' => self::$_SIGN_NAME,
            'TemplateCode' => $this->_templateId,
        );
        date_default_timezone_set('Asia/Shanghai');
        if (!empty($this->_para))
        {
            $para['TemplateParam'] = json_encode($this->_para);
        }

        unset($para['Signature']);

        ksort($para);

        $query = '';
        foreach ($para as $k => $v)
        {
            $k = self::specialUrlEncode($k);
            $v = self::specialUrlEncode($v);

            $query .= $k . '=' . $v . '&';
        }
        $query = substr($query, 0, strlen($query) - 1);
        $queryStr = 'GET&' . self::specialUrlEncode("/") . '&' . self::specialUrlEncode($query);
        $signStr = self::getSignature(self::$_ACCESSKEY_SECRET . "&", $queryStr);
        $queryPara = 'Signature=' . self::specialUrlEncode($signStr) . '&' . $query;

        $res = Tool_Http::get('http://dysmsapi.aliyuncs.com/', $queryPara);
        Tool_Log::addFileLog('sms/notice_log_'.date('Ym'), $this->_mobile.'-'.$this->_templateId.'-'.$res);
        return $res;
    }

    private static function specialUrlEncode($str)
    {
        $str = urlencode($str);
        $str = str_replace("+", "%20", $str);
        $str = str_replace("*", "%2A", $str);
        $str = str_replace("%7E", "~", $str);

        return $str;
    }

    private static function getSignature($key, $str)
    {
        $signature = "";
        if (function_exists('hash_hmac'))
        {
            $signature = base64_encode(hash_hmac("sha1", $str, $key, true));
        }
        else
        {
            $blocksize = 64;
            $hashfunc = 'sha1';
            if (strlen($key) > $blocksize)
            {
                $key = pack('H*', $hashfunc($key));
            }
            $key = str_pad($key, $blocksize, chr(0x00));
            $ipad = str_repeat(chr(0x36), $blocksize);
            $opad = str_repeat(chr(0x5c), $blocksize);
            $hmac = pack('H*', $hashfunc(($key ^ $opad) . pack('H*', $hashfunc(($key ^ $ipad) . $str))));
            $signature = base64_encode($hmac);
        }

        return $signature;
    }
}