<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 17/6/1
 * Time: 13:59
 */
class App_Corp_Page extends App_Corp_Web
{
    protected $headTmpl = 'head/head_common.html';
    protected $tailTmpl = 'tail/tail_common.html';
    protected $title = "";
    protected $csslist = array();
    protected $headjslist = array();
    protected $footjslist = array();

    function __construct($lgmode)
    {
        parent::__construct($lgmode);

        Tool_CssJs::setCssJsHost(WEB_CORP_HOST);
        $this->setCssJs();
    }

    protected function checkAuth()
    {
        parent::checkAuth();

        if ($this->lgmode == 'pri' && (!$this->_uid))
        {
            $http = $_SERVER['HTTPS'] == 'on' ? 'https:://' : 'http://';
            $referer = $http . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
            if (!empty($_SERVER['QUERY_STRING']))
            {
                $referer .= '?' . $_SERVER['QUERY_STRING'];
            }

            header('Location: http://' . WEB_CORP_HOST . '/user/login.php?return_url=' . $referer);
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
        );

        $this->headjslist = array(
            'js/base.js',
        );
    }

    protected function addCss($cssList)
    {
        $this->csslist = array_merge($this->csslist, $cssList);
    }

    protected function addHeadJs($jsList)
    {
        if (is_string($jsList))
        {
            $jsList = array($jsList);
        }
        $this->headjslist = array_merge($this->headjslist, $jsList);
    }

    protected function addFootJs($jsList)
    {
        if (is_string($jsList))
        {
            $jsList = array($jsList);
        }
        $this->footjslist = array_merge($this->footjslist, $jsList);
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
            header("Content-Type: text/html; charset=" . SYS_CHARSET);
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
        $this->smarty->assign('title', $this->title);
        $this->smarty->assign('cssHtml', Tool_CssJs::getCssHtml($this->csslist));
        $this->smarty->assign('jsHtml', Tool_CssJs::getJsHtml($this->headjslist));
        $this->smarty->assign('_user', $this->_user);

        $this->smarty->display($this->headTmpl);
    }

    protected function outputTail()
    {
        $jsHtml = Tool_CssJs::getJsHtml($this->footjslist, TRUE);
        $this->smarty->assign('jsHtml', $jsHtml);

        $jsEnv = array("wwwHost" => CORP_WEB_HOST);
        $this->smarty->assign("jsEnv", $jsEnv);
        $this->smarty->display($this->tailTmpl);
    }

    protected function setCommonPara()
    {
        parent::setCommonPara();
    }

    protected function showError($ex)
    {
        if (defined('DEBUG_MODE') && DEBUG_MODE)
        {
            echo "<!-- \n";
            var_export($ex);
            echo "-->\n";
        }
        $GLOBALS['t_exception'] = $ex;
        header("Location: /common" . DS . "500.php");
        Tool_Log::debug('@app_page', "code:" . $ex->getCode() . "\nerror:" . $ex->getMessage() . "\n" . var_export($ex->getTrace(), TRUE));
        exit;
    }
}