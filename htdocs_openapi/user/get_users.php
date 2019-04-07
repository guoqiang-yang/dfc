<?php

include_once ('../../global.php');


class App extends App_Openapi
{
    private $uids;
    private $needCustomerInfo;
    
    private $users;
    
    protected function getPara()
    {
        $uids = Tool_Input::clean('r', 'uids', TYPE_STR);
        
        $this->uids = explode(',', $uids);
        $this->needCustomerInfo = Tool_Input::clean('r', 'withcustomer', TYPE_UINT);
    }
    
    protected function main()
    {
        $this->users = Open_User_Api::getUsers($this->uids, $this->needCustomerInfo);
        
    }
    
    protected function outputBody()
	{
		$this->result = Conf_Api_Message::genOuterResultDesc(Conf_Api_Message::Outer_Api_St_Succ);
		
		$this->result->result = $this->users;
		
		return $this->result;
	}
}

$app = new App();
$app->run();