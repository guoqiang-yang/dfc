<?php

/**
 * CRM ajax后台程序基类
 */
class App_Crm_Ajax extends App_Crm_Web
{
    protected $allowedMethods = array(
        'HEAD',
        'GET',
        'POST'
    );

    protected function checkAuth()
    {
        if (!in_array($_SERVER["REQUEST_METHOD"], $this->allowedMethods))
        {
            header("HTTP/1.1 405 Method Not Allowed");
            header("Allow: " . implode($this->allowedMethods, ", "));
            exit;
        }

        parent::checkAuth();

        if ($this->lgmode == 'pri' && !$this->_uid)
        {
            echo 'Need Login~';
            exit;
        }
    }

    protected function setAllowedMethod($method)
    {
        $this->allowedMethods = array_map("strtoupper", (array)$method);
    }

    protected function outputPage()
    {
        $this->outputBody();
    }

    protected function showError($ex)
    {
        $rawmsg = $ex->getMessage();
        if (isset(Conf_Exception::$exceptions[$rawmsg]))
        {
            list($errno, $errmsg) = Conf_Exception::$exceptions[$rawmsg];
        }
        else
        {
            $errno = $ex->getCode();
            $errmsg = $rawmsg;//Conf_Exception::DEFAULT_ERRMSG;
        }

        $response = new Response_Ajax();
        $errorArr = array(
            "errmsg" => $errmsg,
            "errno" => $errno,
        );
        if (defined('DEBUG_MODE') && DEBUG_MODE)
        {
            $errorArr["trace"] = $rawmsg . "\n" . var_export($ex->getTrace(), TRUE);
        }
        $response->setError($errorArr);
        $response->send();
        Tool_Log::debug('@app_crm_ajax', "code:" . $ex->getCode() . "\nerror:" . $ex->getMessage() . "\n" . var_export($ex->getTrace(), TRUE));
    }
}