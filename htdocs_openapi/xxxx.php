<?php

/**
 * 通用：接口访问.
 * 
 * @notice 非线上！！！
 * @author guoqiang yang
 */

include_once ('../global.php');

class App extends App_Openapi
{
    private $className;
    private $funcName;
    private $params;    //按照调用顺序
    
    private $result;
    
    protected function getPara()
    {
        $this->className = Tool_Input::clean('r', 'class', TYPE_STR);
        $this->funcName = Tool_Input::clean('r', 'func', TYPE_STR);
        $this->params = json_decode(Tool_Input::clean('r', 'params', TYPE_STR), true);
    }
    
    protected function checkPara()
    {
        if (ENV == 'online')
        {
            echo "It Is a Joke!!\n"; exit;
        }
        
        if (empty($this->className) || empty($this->funcName) || empty($this->params))
        {
            echo "Params Error!!\n"; exit;
        }
    }
    
    protected function main()
    {
        try{
            if (strtolower(substr($this->className, -3)) == 'api')
            {
                $this->result['data'] = call_user_func_array("$this->className::$this->funcName", $this->params);
            }
            else
            {
                $o = new $this->className();
                $this->result['data'] = call_user_func_array(array($o, $this->funcName), $this->params);
            }
            
            $this->result['st'] = 0;
            
        } catch (Exception $e) {
            $this->result['st'] = $e->getCode();
            $this->result['msg'] = $e->getMessage();
        }
    }
    
    protected function outputBody()
	{
        $retCode = $this->result['st'];
       
        $result = Conf_Api_Message::genOuterResultDesc($retCode);
        $result->inner_msg = $this->result['msg'];
        $result->result = $this->result['data'];
        
        return $result;
	}
}

$app = new App();
$app->run();