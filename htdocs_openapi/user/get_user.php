<?php

include_once ('../../global.php');


class App extends App_Openapi
{
    
    private $uid;
    
    private $userInfo;
    
    protected function getPara()
    {
        $this->uid = Tool_Input::clean('r', 'uid', TYPE_UINT);
    }
    
    protected function main()
    {
        $this->userInfo = Open_User_Api::getUser($this->uid);
    }
    
    protected function outputBody()
	{
		$this->result = Conf_Api_Message::genOuterResultDesc(Conf_Api_Message::Outer_Api_St_Succ);
		
		$this->result->result = $this->userInfo;
		
		return $this->result;
	}
}

$app = new App();
$app->run();