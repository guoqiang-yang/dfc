<?php

/**
 * 管理运营后台 - 普通网页程序基类
 */

class App_Coopworker_H5 extends App_Coopworker_Web
{   
	protected $headTmpl = 'head/head_page.html';
	protected $tailTmpl = 'tail/tail_page.html';
	protected $title = "";
	protected $page = "";

	protected $csslist = array();
	protected $headjslist = array('js/base.js');
	protected $footjslist = array();
    
    
	function __construct($lgmode='pri')
	{
		if (date("Y-m-d H:i:s") <= "2016-12-14 22:00:00")
		{
			parent::__construct($lgmode, COOPWORKER_TPL_H5_PATH);

			Tool_CssJs::setCssJsHost(COOPWORDER_H5_HOST);
			$this->setCssJs();
		}
		else
		{
			header('Location: /index.php');
			exit;
		}
	}

	protected function outputHead()
	{
		$this->title = empty($this->title) ? '好材-管理系统' : $this->title;

        list($module, $page) = $this->getCurrentPage();
        
        $this->smarty->assign('curPage', $page);
		$this->smarty->assign('curModule', $module);
        $this->smarty->assign('modules', !empty($this->_uid)? Conf_Coopworker_Page::getModules():array());
		$this->smarty->assign('title', $this->title);
		$this->smarty->assign('cssHtml', Tool_CssJs::getCssHtml($this->csslist));
		$this->smarty->assign('jsHtml', Tool_CssJs::getJsHtml($this->headjslist));
		$this->smarty->display($this->headTmpl);
	}

    
	protected function checkAuth()
	{
		parent::checkAuth();
        
		if ($this->lgmode == 'pri' && empty($this->_uid))
		{
			header('Location: http://'.COOPWORDER_H5_HOST.'/user/login.php');
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
        $exceptPage = $_SERVER['SCRIPT_NAME']=='/user/chgpwd.php' || $_SERVER['SCRIPT_NAME']=='/user/logout.php';
        
        if ( $isSimple && !$exceptPage)
        {
            header('Location: /user/chgpwd.php');
            exit;
        }
        
    }
    
	protected function outputTail()
	{
		$jsHtml = Tool_CssJs::getJsHtml($this->footjslist, true);
		$this->smarty->assign('jsHtml', $jsHtml);

		$jsEnv = array("wwwHost" => COOPWORDER_H5_HOST);
		$this->smarty->assign("jsEnv", $jsEnv);
		$this->smarty->display($this->tailTmpl);
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
    
    protected function getModules()
    {
        return array(
                array('name'=>'我的订单', 'url'=>'/order/my_order_list.php', 'page'=>'my_order_list'),
	            array('name'=>'领单', 'url'=>'/order/get_order.php', 'page'=>'get_order'),
            );
    }

}
