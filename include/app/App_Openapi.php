<?php

/**
 * openApi 使用.
 * 
 */

class App_Openapi extends Base_App
{
	private $allowedMethods = array('GET', 'POST');
    
    protected $appId;
    private $appKey;
    
    function __construct()
    {
        // 设置cityid 给默认值北京
        City_Api::setCity(Conf_City::BEIJING);
        
        //暂且关闭日志 addby guoqiang/2018-02-24
        //$this->printLog();
    }

    private function printLog()
	{
		$info = sprintf("\n-------------api: %s--------------\n", $_SERVER['REQUEST_URI']);
		$info .= "params = " . var_export($_REQUEST, true) . "\n";
		Tool_Log::addFileLog('openapi_call', $info);
	}
    
    protected function checkAuth()
	{
		// 内部地址
		if ($_SERVER['SERVER_ADDR'] == '127.0.0.1')
		{
			return;
		}	
        
        // ip校验
        if ($this->_checkAuth4InnerIP())
        {
            return;
        }
        
		$this->appId = isset($_REQUEST['appid'])? $_REQUEST['appid']: 0;
		$this->appKey = isset($_REQUEST['appkey'])? $_REQUEST['appkey']: '';
		if ( !array_key_exists($this->appId, Conf_Openapi::$Openapi_Secret_Key) 
			|| !$this->appKey == Conf_Openapi::$Openapi_Secret_Key[$this->appId])
		{
			header("HTTP/1.1 401 Unauthorized");
			exit;
		}
        
        if (ENV=='online' && !array_key_exists($this->appId, Conf_Openapi::$onlineAppId))
        {
            header("HTTP/1.1 401 Unauthorized");
			exit;
        }
		
		if (!in_array($_SERVER["REQUEST_METHOD"], $this->allowedMethods))
		{
			header("HTTP/1.1 405 Method Not Allowed");
			exit;
		}
	}

	protected function free()
	{
		Data_DB::free();
	}

	protected function outputHttp()
	{
		header("Content-Type: application/json; charset=".SYS_CHARSET);
        header("Cache-Control: no-cache");
        header("Pragma: no-cache");
	}
    
	protected function outputHead()
	{
		
	}
	
	protected function outputPage()
	{
		$result = $this->outputBody();
		
		$response = new Response_Ajax();
		$result = $response->safeJSONEncode($result);
		
		echo $result;
		exit;
	}
    
    protected function showError($ex)
	{
        $result = Conf_Api_Message::genOuterResultDesc(Conf_Api_Message::Outer_Api_St_Fail);
        
		$response = new Response_Ajax();
        
		echo $response->safeJSONEncode($result);
		exit;
	}
    
    /**
     * 检测内部ip.
     */
    private function _checkAuth4InnerIP()
    {      
        $remoteAddr = array('121.40.136.29', '118.31.236.199');
        $serverAddr = array('120.26.211.129', '121.40.136.29', '118.31.189.144');
        
        if (ENV == 'test')
        {
            return in_array($_SERVER['SERVER_ADDR'], $serverAddr) ? true: false;
        }
        else
        {
            return in_array($_SERVER['REMOTE_ADDR'], $remoteAddr)&&
                   in_array($_SERVER['SERVER_ADDR'], $serverAddr) ? true: false;
        }
    }
}