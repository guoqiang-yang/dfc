<?php

include_once ('../../global.php');


class App extends App_Openapi
{
    
    private $mobile;
    private $source;
    
    private $result;
    
    protected function getPara()
    {
        $this->mobile = Tool_Input::clean('r', 'mobile', TYPE_STR);
        $this->source = Tool_Input::clean('r', 'source', TYPE_UINT);
    }
    
    protected function main()
    {
        
        
        $this->result = Open_User_Api::quickLogin($this->mobile, $this->source);
    }
    
    protected function outputBody()
	{
        $retCode = $this->result['st'];
       
        $result = Conf_Api_Message::genOuterResultDesc($retCode);
        $result->result = $this->result['data'];
        
        return $result;
	}
}

$app = new App();
$app->run();