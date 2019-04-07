<?php

/**
 * 管理运营后台 - 普通网页程序基类
 */

class App_Admin_Page_H5 extends App_Admin_Page
{
	protected $headTmpl = 'head/head_page.html';
	protected $tailTmpl = 'tail/tail_page.html';

	function __construct($lgmode='pri')
	{
		parent::__construct($lgmode, ADMIN_TEMPLATE_H5_PATH, ADMIN_HOST_H5);
	}

	protected function outputHead()
	{
		$this->title = empty($this->title) ? '好材-运营系统' : $this->title;

		list($module, $page) = $this->getCurrentPage();

		$this->smarty->assign('curPage', $page);
		$this->smarty->assign('curModule', $module);
		$this->smarty->assign('modules', Conf_Admin_Page_H5::getMODULES($this->_uid, $this->_user));
		$this->smarty->assign('title', $this->title);
		$this->smarty->assign('cssHtml', Tool_CssJs::getCssHtml($this->csslist));
		$this->smarty->assign('jsHtml', Tool_CssJs::getJsHtml($this->headjslist));
		$this->smarty->display($this->headTmpl);
	}

	//todo: 域名抽象出来
	protected function checkAuth()
	{
		$this->_uid = $this->getLoginUid();

        // admin-h5没有继承父类的checkAuth，所以在此处记录
        $this->printLog();
        
		// 已登录状态处理
		if ($this->_uid)
		{
			//获取用户信息
			$this->_user = Admin_Api::getStaff($this->_uid);
		}


		if ($this->lgmode == 'pri' && empty($this->_uid))
		{
			header('Location: http://m.'.Conf_Base::getAdminHost().'/user/login.php');
			exit;
		}
	}

	protected function outputTail()
	{
		$jsHtml = Tool_CssJs::getJsHtml($this->footjslist, true);
		$this->smarty->assign('jsHtml', $jsHtml);

		$jsEnv = array("wwwHost" => ADMIN_HOST);
		$this->smarty->assign("jsEnv", $jsEnv);
		$this->smarty->display($this->tailTmpl);
	}

}
