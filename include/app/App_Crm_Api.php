<?php

/**
 * htdocs_crm 使用.
 *
 */
class App_Crm_Api extends Base_App
{
    private $responseData;
    private $loginmode;

    function __construct($loginmode = 'pri')
    {
        $this->loginmode = $loginmode;

        // 设置cityid 给默认值北京
        City_Api::setCity(Conf_City::BEIJING);
        $this->printLog();
    }

    private function printLog()
    {
        $info = sprintf("\n-------------api: %s--------------\n", $_SERVER['REQUEST_URI']);
        $info .= "params = " . var_export($_REQUEST, TRUE) . "\n";
        Tool_Log::addFileLog('crm_api_call', $info);
    }

    protected function outputHttp()
    {
        header("Content-Type: application/json; charset=" . SYS_CHARSET);
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
    }

    protected function checkAuth()
    {
        if (!$this->_uid)
        {
            $verify = Tool_Input::clean('r', 'token', TYPE_STR);
            $this->_uid = Admin_Auth_Api::checkVerify($verify, Conf_Base::APP_TOKEN_EXPIRED);
            if ($this->loginmode == 'pri' && empty($this->_uid))
            {
                $response = new stdClass();
                $response->errno = 7;
                $response->errmsg = '登录信息已过期';
                $response->data = $this->responseData;
                $hResponse = new Response_Ajax();
                echo $hResponse->safeJSONEncode($response);

                exit;
            }

            if (!empty($this->_uid))
            {
                $suserInfo = Admin_Api::getStaff($this->_uid);
                if (!Admin_Role_Api::hasRole($suserInfo, Conf_Admin::ROLE_SALES_NEW))
                {
                    $response = new stdClass();
                    $response->errno = 7;
                    $response->errmsg = '登录信息已过期';
                    $response->data = $this->responseData;
                    $hResponse = new Response_Ajax();
                    echo $hResponse->safeJSONEncode($response);

                    exit;
                }
            }
        }
    }

    protected function outputHead()
    {
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
            $response->errmsg = $msg;
        }

        $hResponse = new Response_Ajax();

        echo $hResponse->safeJSONEncode($response);
        $content = "code:" . $ex->getCode() . "\nerror:" . $ex->getMessage() . "\n" . var_export($ex->getTrace(), TRUE);
        Tool_Log::addFileLog("exception_crm_api", $content, TRUE);

        exit;
    }
}