<?php

/**
 * 企业号 - 普通网页程序基类
 */

class App_Franchisee_Page extends App_Franchisee_Web
{
	protected $headTmpl = 'head/head_page.html';
	protected $tailTmpl = 'tail/tail_page.html';
	protected $title = "";
	protected $page = "";

	protected $csslist = array();
	protected $headjslist = array('js/base.js');
	protected $footjslist = array();

	function __construct($lgmode='pub', $tmplpath=FSA_TPL_PATH, $cssjs=FRANCHISEE_HOST)
	{
		parent::__construct($lgmode, $tmplpath);
		Tool_CssJs::setCssJsHost($cssjs);
		$this->setCssJs();
	}

	protected function checkAuth()
	{
		parent::checkAuth();
        
		if ($this->lgmode == 'pri' && empty($this->_uid))
		{
			header('Location: http://'.PLATFORM_HOST.'/user/login.php');
			exit;
		}
        
        $this->isSimplePassword();
	}
    
    /**
     * 检测密码是否过于简单.
     */
    protected function isSimplePassword()
    {
        if (empty($this->_uid) || empty($this->_user))
        {
            return;
        }
        
        // 不进行password 检测
        $passSimple = Tool_Input::clean('r', 'hc111', TYPE_STR);
        if ($passSimple == '6')
        {
            return;
        }
        
        $isSimple = Str_Check::isSimplePasswd4User($this->_user['password'], $this->_user['salt'], $this->_user['mobile']);
        if ( $isSimple && $_SERVER['SCRIPT_NAME'] != '/user/chgpwd.php' && $_SERVER['SCRIPT_NAME'] != '/user/logout.php' )
        {
            header('Location: /user/chgpwd.php');
            exit;
        }
        
    }

	protected function setTitle($title)
	{
		$this->title = $title;
	}

	protected function setCssJs()
	{
		$this->csslist = array(
			//'css/index.css',
		);

		$this->headjslist = $this->headjslist;

		$this->footjslist = array();
	}

	protected function addCss($cssList)
	{
		$this->csslist = array_merge($this->csslist , $cssList);
	}

	protected function addHeadJs($jsList)
	{
		if (is_string($jsList))
		{
			$jsList = array($jsList);
		}
		$this->headjslist = array_merge($this->headjslist , $jsList);
	}

	protected function addFootJs($jsList)
	{
		if (is_string($jsList))
		{
			$jsList = array($jsList);
		}
		$this->footjslist = array_merge($this->footjslist , $jsList);
	}

	protected function removeJs()
	{
		$this->headjslist = array();
		$this->setCssJs();
	}

	protected function setHeadTmpl($tmpl)
	{
		$this->headTmpl = $tmpl;
	}

	protected function setTailTmpl($tmpl)
	{
		$this->tailTmpl = $tmpl;
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

//	protected function outputHead()
//	{
//		$this->title = empty($this->title) ? '好材-加盟商' : $this->title;
//
//		list($module, $page) = $this->getCurrentPage();
//
//		$this->smarty->assign('curPage', $page);
//		$this->smarty->assign('curModule', $module);
//		$this->smarty->assign('modules', Conf_Franchisee_Page::getMODULES($this->_uid, $this->_user));
//		$this->smarty->assign('title', $this->title);
//		$this->smarty->assign('cssHtml', Tool_CssJs::getCssHtml($this->csslist));
//		$this->smarty->assign('jsHtml', Tool_CssJs::getJsHtml($this->headjslist));
//		$this->smarty->display($this->headTmpl);
//	}

	protected function outputTail()
	{
		$jsHtml = Tool_CssJs::getJsHtml($this->footjslist, true);
		$this->smarty->assign('jsHtml', $jsHtml);

		$jsEnv = array("wwwHost" => PLATFORM_HOST);
		$this->smarty->assign("jsEnv", $jsEnv);
        
		$this->smarty->display($this->tailTmpl);
	}

	protected function setCommonPara()
	{
		parent::setCommonPara();
	}

	protected function showError($ex)
	{
		echo "<!-- \n";
		var_export($ex);
		echo "-->\n";

		$GLOBALS['t_exception'] = $ex;
		$this->delegateTo("common" . DS .  "500.php");
		Tool_Log::debug('@app_page', "code:".$ex->getCode()."\nerror:" . $ex->getMessage() . "\n" . var_export($ex->getTrace(),true));
		exit;
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

}
