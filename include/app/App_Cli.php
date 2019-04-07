<?php
/**
 * 后台程序/命令行(command line interface)程序基类
 */

// 设定时区
date_default_timezone_set('PRC');

// 报错模式
error_reporting(E_ERROR);

class App_Cli extends Base_Func
{
    protected $responseData = array();

    function run()
    {
        try
        {
            $this->getPara();
            $this->main();
        }
        catch (Exception $ex)
        {
            $this->showError($ex);
        }
    }

    protected function getPara()
    {
    }

    protected function main()
    {
    }

    protected function showError($ex)
    {
        $error = "[" . $ex->getCode() . "]: " . $ex->getMessage();
        if ($ex->reason)
        {
            $error .= ' ' . $ex->reason;
        }

        echo $error . "\n";
        print_r($ex->getTrace());
        echo "\n";
    }

    protected function _trace($textFormat /*$param*/)
    {
        $format = '[' . date('Y-m-d H:i:s') . '] [Trace] ' . $textFormat . "\n";
        $params = array_slice(func_get_args(), 1);
        $log = vsprintf($format, $params);
        echo $log;
    }

    protected function _error($textFormat /*$param*/)
    {
        $format = '[' . date('Y-m-d H:i:s') . '] [Error] ' . $textFormat . "\n";
        $params = array_slice(func_get_args(), 1);
        $log = vsprintf($format, $params);
        echo $log;
    }

    protected function getDbAllDatas($table, $filed, $where, $order = '', $start = 0, $num = 100, $isUpStart = TRUE)
    {
        do
        {
            $ret = $this->one->select($table, $filed, $where, $order, $start, $num);

            $hasUpdateRecord = $this->callbackGetAllDatas($ret['data']);

            // 是否更新$start游标；一般是要更新的，不更新改字段
            // 在up全表数据是，因为更新了表数据，在取数据时，会导致更新数据丢失
            if ($isUpStart)
            {
                $start += $num;
            }
            else
            {
                if (!$hasUpdateRecord || empty($hasUpdateRecord))
                {
                    break;
                }
            }
        }
        while (!empty($ret['data']));

        return $this->responseData;
    }

    protected function callbackGetAllDatas($ret = array())
    {
        $this->responseData = array_merge($this->responseData, $ret);
    }
}
