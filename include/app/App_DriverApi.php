<?php
/**
 * Created by PhpStorm.
 * User: joker
 * Date: 16/11/4
 * Time: 下午5:43
 *
 * 司机app 使用的 api 基类.
 */

class App_DriverApi extends Base_App
{
    protected $lgmode;		//页面逻辑模式 -- pub (公开页面,不需登录); pri (私有页面,需要登录)

    /**
     * 通用变量.
     */
    protected $version;
    protected $cityid;
    protected $token;
    protected $model;
    protected $sysversion;
    protected $userInfo;
    protected $userType;


    /**
     * response->data
     */
    protected $responseData;

    protected $cid;
    protected $uid;

    function __construct($lgmode='pri')
    {
        $this->lgmode = $lgmode;

        $this->version = $_REQUEST['version'];
        $this->cityid = $_REQUEST['cityid']?:Conf_City::BEIJING;
        $this->token = $_REQUEST['token'];
        $this->model = $_REQUEST['model'];
        $this->sysversion = $_REQUEST['sysversion'];

        // 设置cityid
        City_Api::setCity($this->cityid);
        
        //暂且关闭日志 addby guoqiang/2018-02-24
        //$this->printLog();
    }

    private function printLog()
    {
        $info = sprintf("\n-------------api: %s--------------\n", $_SERVER['REQUEST_URI']);
        $info .= "params = " . var_export($_REQUEST, true) . "\n";
        Tool_Log::addFileLog('driver_api_call', $info);
    }

    protected function checkAuth()
    {
        if ($_SERVER['SERVER_ADDR']!='127.0.0.1'
            && empty($this->version) )
        {
            throw new Exception('common:params error');
        }

        if ($this->lgmode == 'pri')
        {
            $this->_checkUserInfo();
            $this->_setUserInfo();
        }
    }

    private function _checkUserInfo()
    {
        if (empty($this->token))
        {
            throw new Exception('common:to login');
        }

        $userInfo = Logistics_Auth_Api::checkVerifyApp($this->token, Conf_Base::WEB_TOKEN_EXPIRED);
        $this->userInfo = $userInfo['user_info'];
        if($userInfo === false)
        {
            throw new Exception('common:to login');
        }


    }

    private function _setUserInfo()
    {
        $tokens = explode('_', $this->token);
        $this->uid = $tokens[2];
        $this->userType = $tokens[3];
    }

    protected function outputHttp()
    {
        if (!headers_sent())
        {
            header("Content-Type: application/json; charset=".SYS_CHARSET);
            header("Cache-Control: no-cache");
            header("Pragma: no-cache");
        }
    }

    protected function setResult($data)
    {
        $this->responseData = $data;
    }

    protected function outputPage()
    {
        // response
        $response = new stdClass();
        $response->errno = 0;
        $response->errmsg = '成功';
        $response->data = $this->responseData;

        $hResponse = new Response_Ajax();

        echo $hResponse->safeJSONEncode($response);

        $ret = json_encode($this->responseData);
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            $info = sprintf("-------------one api ret, API: %s  --------------\n",
                $_SERVER['REQUEST_URI']);
            $info .= "ret = " . $ret . "\n";
            Tool_Log::addFileLog('apicall', $info);
        }

        exit;
    }

    protected function showError($ex)
    {
        $response = new stdClass();

        $msg = $ex->getMessage();
        if (array_key_exists($msg, Conf_Exception::$exceptions))
        {
            list($response->errno, $response->errmsg) = Conf_Exception::$exceptions[$msg];
        }
        else
        {
            $response->errno = Conf_Exception::DEFAULT_ERRNO;
            $response->errmsg = Conf_Exception::DEFAULT_ERRMSG;
        }

        $hResponse = new Response_Ajax();

        echo $hResponse->safeJSONEncode($response);
        $content = "code:".$ex->getCode()."\nerror:" . $ex->getMessage() . "\n" . var_export($ex->getTrace(),true);
        Tool_Log::addFileLog("exception_appapi" , $content, true);
        exit;
    }
}
