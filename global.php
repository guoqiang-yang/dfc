<?php
include_once(dirname(__FILE__) . "/conf.php");
include_once(dirname(__FILE__) . "/common.php");

//autoload
function __autoload($className)
{
	//找到目录
	$pos = strpos($className, '_');
	if(!$pos)
	{
		return false;
	}
	$dir = substr($className, 0, $pos);
	$dir = strtolower($dir);

	//加载
	$classFile = INCLUDE_PATH .	$dir . '/' . $className . '.php';

	if (is_file($classFile))
	{
		require_once($classFile);
		return true;
	}
	return false;
}

spl_autoload_register('__autoload');

function my_assert_handler($file, $line, $code)
{
	if (defined('DEBUG_MODE') && DEBUG_MODE)
	{
		$info = "<!--Assertion Failed:
			File '$file'<br />
			Line '$line'<br />
			Code '$code'<br />";
		$trace = debug_backtrace();
		foreach($trace as &$item) unset($item['object']);
		$info.= var_export($trace, true) . "\n";
        
		Tool_Log::addFileLog('assert', $info);
	}
	throw new Exception('common:system error');
}

// Set up the callback
assert_options(ASSERT_CALLBACK, 'my_assert_handler');

function _assertLog($file, $line)
{
    $info = "Assert Position:\n\tFile: ". str_replace(ROOT_PATH, '', $file). "\t##Line: $line\n".
    
    $traces = array();
    foreach(debug_backtrace() as $item)
    {
        if (empty($item['file']))
        {
            $traces = array(); continue;
        }
        
        $traces[] = str_replace(ROOT_PATH, '', $item['file']). "\t##". $item['line'];
    }
    
    //last one is the visited page
    $info .= "Visited Page:\n\t". str_replace(ROOT_PATH, '', $item['file']). "\n";
    $info .= "CallStack: \n\t". implode("\n\t", $traces). "\n";
    
    return $info;
}

