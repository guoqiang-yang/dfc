<?php
/**
 * 输出csv文件
 */
class Data_Csv
{
    private static $_CNT = 0;
    private static $LIMIT = 1000;

    public static function send($arr)
    {
        if (BASE_HOST != '.haocaisong.cn')
        {
            exit;
        }

        $fp = fopen('php://output', 'a');

        foreach ($arr as $i => $v)
        {
            $arr[$i] = iconv('utf-8', 'gbk', $v);
        }

        // 将数据通过fputcsv写到文件句柄
        $delimiter = ',';
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        if(strpos($agent, 'macintosh'))
        {
            $delimiter = ';';
        }

        fputcsv($fp, $arr, $delimiter);

        self::$_CNT++;

        //刷新一下输出buffer，防止由于数据过多造成问题
        if (self::$_CNT >= self::$LIMIT)
        {
            ob_flush();
            flush();
            self::$_CNT = 0;
        }
    }
}