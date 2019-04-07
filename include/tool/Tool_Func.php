<?php
/**
 * Func 函数
 */
class Tool_Func
{
	public static function filterPara($srcPara, $paraConf)
	{
		$dstPara = array();
		foreach ($paraConf as $var_name => $var_type)
		{
			if (!isset($srcPara[$var_name]))
			{
				continue;
			}

			$var_value = $srcPara[$var_name];
			Tool_Input::cast($var_value, $var_type);
			$dstPara[$var_name] = $var_value;
		}
		return $dstPara;
	}
    
    public static function setIntVal(&$objVal, $k, $val)
    {
        if (array_key_exists($k, $objVal))
        {
            $objVal[$k] += $val;
        }
        else
        {
            $objVal[$k] = $val;
        }
    }
}
