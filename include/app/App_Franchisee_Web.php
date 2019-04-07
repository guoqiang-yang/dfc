<?php

/**
 * 平台/加盟 - Web基类
 */
class App_Franchisee_Web extends Base_App
{
	protected $lgmode;		//页面逻辑模式 -- pub (公开页面,不需登录); pri (私有页面,需要登录)
	protected $smarty;		//smarty 工具对象
    protected $headTmpl = 'head/head_page.html';
    protected $tailTmpl = 'tail/tail_page.html';
    protected $title = "";
    protected $page = "";
    
    protected $_uid;       //合作工人的ID
    protected $_user;      //合作工人的个人信息
    protected $csslist = array();
    protected $headjslist = array('js/base.js');
    protected $footjslist = array();

	function __construct($lgmode='pub', $tmplpath=FSA_TPL_PATH)
	{
		$this->lgmode = $lgmode;
		$this->smarty = new Tool_Smarty($tmplpath);

		//$this->printLog();
	}

	private function printLog()
	{
        //@todo Add Log
	}

	protected function checkAuth()
	{
		$this->getLoginUserInfo();
        
        if (!empty($this->_user))
        {
            City_Api::setCity($this->_user['city_id']);
        }
	}

	protected function free()
	{
		Data_DB::free();
	}

	protected function setCommonPara()
	{
		$this->smarty->assign('_imgHost', ADMIN_IMG_HOST);
		$this->smarty->assign("_wwwHost", FRANCHISEE_HOST);
		$this->smarty->assign("_uid", $this->_uid);
		$this->smarty->assign("_user", $this->_user);
        
	}

    protected function outputHttp()
    {
        if (!headers_sent())
        {
            header("Content-Type: text/html; charset=".SYS_CHARSET);
            if ($this->_uid > 0)
            {
                header("Cache-Control: no-cache; private");
            }
            else
            {
                header("Cache-Control: no-cache");
            }
            header("Pragma: no-cache");
        }
    }

    protected function outputHead()
    {
        $this->title = empty($this->title) ? '好材-加盟商' : $this->title;
        if (defined('TITLE_PREFIX') && TITLE_PREFIX)
        {
            $this->title = TITLE_PREFIX . $this->title;
        }

        list($module, $page) = $this->getCurrentPage();

        $this->smarty->assign('curPage', $page);
        $this->smarty->assign('curModule', $module);
        $this->smarty->assign('modules', Conf_Franchisee_Page::getMODULES($this->_uid, $this->_user));
        $this->smarty->assign('title', $this->title);
        $this->smarty->assign('cssHtml', Tool_CssJs::getCssHtml($this->csslist));
        $this->smarty->assign('jsHtml', Tool_CssJs::getJsHtml($this->headjslist));

        $cur_city = '';
        if(isset($this->_user['city_id']))
        {
            $city_list = Conf_City::$CITY;
            $cur_city = $city_list[$this->_user['city_id']];
        }
        $this->smarty->assign('cur_city', $cur_city);

        $this->smarty->display($this->headTmpl);
    }

    protected function outputTail()
    {
        $jsHtml = Tool_CssJs::getJsHtml($this->footjslist, true);
        $this->smarty->assign('jsHtml', $jsHtml);

        $jsEnv = array("wwwHost" => ADMIN_HOST);
        $this->smarty->assign("jsEnv", $jsEnv);
        $this->smarty->display($this->tailTmpl);

        // 线上调试使用
        $isDebug = Tool_Input::clean('r', 'debug', TYPE_UINT);
        if ($isDebug == 1)
        {
            print_r($this);
        }
    }

    protected function getCurrentPage()
    {
        $res = parse_url($_SERVER['REQUEST_URI']);
        $module = trim(dirname($res['path']), "\/");
        $page = basename($res['path'], '.php');

        if (! empty($this->page))
        {
            $page = $this->page;
        }

        return array($module, $page);
    }

	protected function showError($ex)
	{
		$error = "[".$ex->getCode()."]: ".$ex->getMessage();
		if ($ex->reason)
		{
			$error .= ' '.$ex->reason;
		}
		echo $error."\n";

		print_r($ex->getTrace());
		echo "\n";
	}

	protected function getLoginUserInfo()
	{
        //@todo For isLogin

		$verify = Tool_Input::clean('c', '_fsa_session', TYPE_STR);
        $user = Franchisee_Auth_Api::checkVerify($verify, Conf_Base::WEB_TOKEN_EXPIRED);

        if (!empty($user) && isset($user['fid']) && !empty($user['fid']))
        {
            $this->_uid = $user['fid'];
            $user['suid'] = $this->_uid;
            $this->_user = $user['user_info'];
            
            return;
        }
        
		self::clearVerifyCookie();
		return false;
	}
    
	protected function setSessionVerifyCookie($token, $expiredTime=0)
	{
		$expiredTime = !empty($expiredTime) ? (time() + $expiredTime) : 0;
        
		setcookie("_fsa_session", $token, $expiredTime, "/", Conf_Base::getBaseHost());
		setcookie('_fsa_uid', $this->_uid, $expiredTime, '/', Conf_Base::getBaseHost());
	}

	protected static function clearVerifyCookie()
	{
		setcookie('_fsa_session', '', -86400, '/', Conf_Base::getBaseHost());
		setcookie('_fsa_uid', '', -86400, '/', Conf_Base::getBaseHost());
	}

    protected function delegateTo($path)
    {
        chdir(ADMIN_HTDOCS_PATH . "/" . dirname($path));

        require_once ADMIN_HTDOCS_PATH . "/" . $path;
    }
}
