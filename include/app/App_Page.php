<?php

/**
 * 普通网页程序基类
 */
class App_Page extends App_Web
{
    protected $headTmpl = 'head/head_common.html';
    protected $tailTmpl = 'tail/tail_common.html';
    protected $title = "";
    protected $module = "";
    protected $status = NULL;        //当前登陆用户状态
    protected $csslist = array();
    protected $headjslist = array();
    protected $footjslist = array();

    function __construct($lgmode)
    {
        parent::__construct($lgmode);
        $this->setCssJs();
    }

    public function run()
    {
        if ($_REQUEST['xf'] && ENV == 'test')
        {
            xhprof_enable(XHPROF_FLAGS_NO_BUILTINS + XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
            global $HC_SQL_EXTIMES;
            parent::run();
            $xhprofData = xhprof_disable();
            require '/usr/share/xhprof/xhprof_lib/utils/xhprof_lib.php';
            require '/usr/share/xhprof/xhprof_lib/utils/xhprof_runs.php';

            $xhprofRuns = new XHProfRuns_Default();
            $runId = $xhprofRuns->save_run($xhprofData, 'xhprof_test');

            echo '<div style="width: 100%; position: fixed; bottom: 2rem; text-align: center; font-size: 18px;"><a target="_blank" href="http://x.test.haocaisong.cn/index.php?run=' . $runId . '&source=xhprof_test">-----------性能分析-----------</a></div>';
            uasort($HC_SQL_EXTIMES, 'sortSql');
            echo "\n\n<!--";
            foreach ($HC_SQL_EXTIMES as $item)
            {
                $extime = round($item['extime'] * 1000, 2);
                echo "[{$extime} ms] => {$item['sql']};\n";
            }
            echo "-->\n\n";
        }
        else
        {
            parent::run();
        }
    }

    protected function checkAuth()
    {
        parent::checkAuth();

        if ($this->lgmode == 'pri' && (!$this->_uid || !$this->_cid))
        {
            $http = $_SERVER['HTTPS'] == 'on' ? 'https:://' : 'http://';
            $referer = $http . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
            if (!empty($_SERVER['QUERY_STRING']))
            {
                $referer .= '?' . $_SERVER['QUERY_STRING'];
            }

            header('Location: http://' . C_H5_MAIN_HOST . '/login/login.php?return_url=' . $referer);
            exit;
        }

        //城市
        $city = isset($_COOKIE['shop_city_id'])&&!empty($_COOKIE['shop_city_id'])?$_COOKIE['shop_city_id']:     //h5
                (isset($_COOKIE['city_id'])&&!empty($_COOKIE['city_id']) ? $_COOKIE['city_id']: Conf_City::BEIJING); //app
        City_Api::setCity($city);
    }

    protected function setTitle($title)
    {
        $this->title = $title;
    }

    protected function setCssJs()
    {
        $this->csslist = array(
            'css/style.css',
        );

        $this->headjslist = array(
            "js/base.js", 'js/common.js',
        );

        $this->footjslist = array(
            "js/main.js", 'js/cart.prototype.js',
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
        $this->smarty->assign('module', $this->module);
        $this->smarty->assign('cssHtml', Tool_CssJs::getCssHtml($this->csslist));
        $this->smarty->assign('jsHtml', Tool_CssJs::getJsHtml($this->headjslist));
        $cityInfo = City_Api::getCity();
        $userCart = Cart_Api::getUserCart($this->_uid, $cityInfo['city_id'], Conf_Activity_Flash_Sale::PALTFORM_WECHAT, true);
        $this->smarty->assign('user_cart', json_encode($userCart));
        $choosePids = $giftPids = $discountPids = array();
        if (!empty($_REQUEST['gift']))
        {
            $giftPids = json_decode($_REQUEST['gift'], TRUE);
        }
        else
        {
            session_start();
            $giftPids = json_decode($_SESSION['gift_pids'], true);
        }
        if (!empty($_REQUEST['discount']))
        {
            $discountPids = json_decode($_REQUEST['discount'], TRUE);
        }
        else
        {
            session_start();
            $discountPids = json_decode($_SESSION['discount_pids'], true);
        }

        if (!empty($_REQUEST['choose_pids']))
        {
            $choosePids = json_decode($_REQUEST['choose_pids'], true);
            $_SESSION['choose_pids'] = $_REQUEST['choose_pids'];
        }
        else
        {
            session_start();
            $choosePids = json_decode($_SESSION['choose_pids'], true);
        }
        if (!empty($giftPids))
        {
            foreach ($giftPids as $pid => $num)
            {
                $choosePids[$pid] += $num;
            }
        }
        if (!empty($discountPids))
        {
            foreach ($discountPids as $pid => $num)
            {
                $choosePids[$pid] += $num;
            }
        }

        $totalPrice = 0;
        $totalNum = 0;
        foreach ($userCart as $item)
        {
            if (empty($choosePids) || array_key_exists($item['pid'], $choosePids))
            {
                $totalNum += $item['num'];
                $totalPrice += $item['num'] * $item['sale_price'] / 100;
            }
        }
        $this->smarty->assign('total_price', $totalPrice);
        $this->smarty->assign('total_num', $totalNum);

        $this->smarty->display($this->headTmpl);
    }

    protected function outputTail()
    {
        $jsHtml = Tool_CssJs::getJsHtml($this->footjslist, TRUE);
        $this->smarty->assign('jsHtml', $jsHtml);
        $this->smarty->assign('module', $this->module);

        $jsEnv = array("wwwHost" => WWW_HOST);
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
        $this->delegateTo("common" . DS . "500.php");
        Tool_Log::debug('@app_page', "code:" . $ex->getCode() . "\nerror:" . $ex->getMessage() . "\n" . var_export($ex->getTrace(), TRUE));
        exit;
    }
}

function sortSql($a, $b)
{
    if ($a['extime'] == $b['extime'])
    {
        return 0;
    }

    return $a['extime'] > $b['extime'] ? -1 : 1;
}