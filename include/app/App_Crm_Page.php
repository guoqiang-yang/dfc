<?php

/**
 * CRM普通网页程序基类
 */
class App_Crm_Page extends App_Crm_Web
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
        $this->setCssJs();
    }

    protected function checkAuth()
    {
        parent::checkAuth();

        if ($this->lgmode == 'pri' && !$this->_uid)
        {
            echo 'Need Login~';
            exit;
        }
    }

    protected function setTitle($title)
    {
        $this->title = $title;
    }

    protected function setCssJs()
    {
        $this->csslist = array('css/style.css',);
        $this->headjslist = array("js/script.js", "js/base.js");
        $this->footjslist = array();
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

        $this->smarty->display($this->headTmpl);
    }

    protected function outputTail()
    {
        $jsHtml = Tool_CssJs::getJsHtml($this->footjslist, TRUE);
        $this->smarty->assign('jsHtml', $jsHtml);

        $this->smarty->display($this->tailTmpl);
    }

    protected function showError($ex)
    {
        if (defined('DEBUG_MODE') && DEBUG_MODE)
        {
            echo "<!-- \n";
            var_export($ex);
            echo "-->\n";
        }
        $msg = $ex->getMessage();
        if (array_key_exists($msg, Conf_Exception::$exceptions))
        {
            echo '<div style="text-align:center;font-size:22px; color: red;">' . Conf_Exception::$exceptions[$msg][1] .'</div>';
        }
        else
        {
            echo '<div style="text-align:center;font-size:22px; color: red;">' . $msg .'</div>';
        }
        $GLOBALS['t_exception'] = $ex;
        Tool_Log::debug('@app_crm_page', "code:" . $ex->getCode() . "\nerror:" . $ex->getMessage() . "\n" . var_export($ex->getTrace(), TRUE));

        exit;
    }
}