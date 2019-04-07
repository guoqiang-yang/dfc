<?php

/**
 * 简单日志工具
 */
class Tool_Log
{
    public static function debug($key, $exinfo)
    {
        self::addFileLog("debug", $key . "\t" . $exinfo, TRUE);
    }

    /**
     * 打印字符串日志到syslog
     *
     * @param     $tag
     * @param     $message
     * @param int $priority
     */
    public static function addSysLog($tag, $message, $priority = LOG_NOTICE)
    {
        if (!is_string($message))
        {
            $message = var_export($message, TRUE);
        }
        openlog("KXC", LOG_PID, LOG_LOCAL6);
        syslog($priority, $tag . "\t" . $message);
        closelog();
    }

    public static function addFileLog($filename, $desc, $micro = FALSE)
    {
        $fp = fopen(MULTILOG_PATH . $filename, 'a');
        if ($fp)
        {
            $time = date('Y-m-d H:i:s');
            if ($micro)
            {
                $time .= "\t" . self::getMicro();
            }
            $flog = sprintf("%s\t%s\t%s\t%s\n", $time, self::getIp(), self::getPid(), $desc);
            $ret = fwrite($fp, $flog);
            fclose($fp);

            return $ret;
        }

        return FALSE;
    }

    /**
     * 管理员访问日志
     *
     * @param            $uid
     *
     * @return bool|int
     */
    public static function addAccessLog($uid)
    {
        //一些定时跑的脚本不记录
        $blackList = array(
            '/logistics/ajax/has_new_unline_order.php',
            '/order/ajax/get_new_oid.php',
            '/user/ajax/get_message_count.php',
        );
        $scriptName = $_SERVER['SCRIPT_NAME'];
        if (in_array($scriptName, $blackList))
        {
            return false;
        }

        $fp = fopen(MULTILOG_PATH . '/admin_access_log/access_log_' . date('Y-m-d'), 'a');
        if ($fp)
        {
            $time = date('Y-m-d H:i:s');
            $ip = self::getIp();
            $params = '';
            if (!empty($_GET))
            {
                $params .= 'GET: ';
                foreach ($_GET as $k => $v)
                {
                    $v = str_replace(array("\t", "\n", "\r"), array('', '', ''), $v);
                    $params .= "$k=$v&";
                }
            }
            if (!empty($_POST))
            {
                $params .= 'POST: ';
                foreach ($_POST as $k => $v)
                {
                    $v = str_replace(array("\t", "\n", "\r"), array('', '', ''), $v);
                    $params .= "$k=$v&";
                }
            }
            $flog = sprintf("%s\t%d\t%s\t%s\t%s\n", $ip, $uid, $time, $scriptName, $params);
            $ret = fwrite($fp, $flog);
            fclose($fp);

            return $ret;
        }

        return FALSE;
    }

    private static function getPid()
    {
        static $pid = NULL;
        if (NULL === $pid)
        {
            $pid = getmypid() . '.' . rand(0, 100000);
        }

        return $pid;
    }

    private static function getIp()
    {
        static $ip = NULL;
        if (NULL === $ip)
        {
            $ip = Tool_Ip::getClientIP();
        }

        return $ip;
    }

    private static function getMicro()
    {
        list($msec, $sec) = explode(" ", microtime());

        return ((float)$msec + (float)$sec);
    }
}
