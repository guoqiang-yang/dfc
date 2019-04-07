<?php

include_once INCLUDE_PATH . 'qrcode/src/qrlib.php';

class Qrcode_Api extends Base_Api
{
	/**
	 * 生成sku二维码
	 *
	 * @param $sid
	 * @param int $orderType
	 * @param string $orderid
	 * @param string $errorCorrectionLevel
	 * @param int $matrixPointSize
	 * @param int $margin
	 * @return string
	 */
	public static function genQrcodeBySid($sid, $orderType = Conf_Qrcode::ORDER_TYPE_NONE, $orderid = Conf_Qrcode::ORDER_ID_NONE, $errorCorrectionLevel = 'Q', $matrixPointSize = 6, $margin = 2)
	{
		//查看二维码图片（文件）是否存在
		$file = self::_getQrcodeFile($sid, $orderType, $orderid);

		//存在，返回二维码图片的url
		if (file_exists($file))
		{
			return self::_getQrcodeUrl($sid, $orderType, $orderid);
		}

		//不存在，创建目录，生成二维码，保存，返回二维码图片的url
		$dir = dirname($file);
		if (!is_dir($dir))
		{
			mkdir($dir, 0777, true);
		}
		$code = self::_genCodeBySid($sid, $orderType, $orderid);
		QRcode::png($code, $file, $errorCorrectionLevel, $matrixPointSize, $margin);

		return self::_getQrcodeUrl($sid, $orderType, $orderid);
	}

	private static function _genCodeBySid($sid, $orderType = Conf_Qrcode::ORDER_TYPE_NONE, $orderid = Conf_Qrcode::ORDER_ID_NONE)
	{
		$sku = Shop_Api::getSkuInfo($sid);
		//品类码
		//sku创建日期-999-0000-$sid
		if ($sku['qrcode_type'] == Conf_Qrcode::QRCODE_TYPE_CATE)
		{
			$cdate = date('Ymd', strtotime($sku['ctime']));

			$code = $cdate . '-' . $orderType . '-' . $orderid . '-' . $sid;
		}
		//单品码
		else
		{
			$code = date('Ymd') . '-' . $orderType . '-' . $orderid . '-' . $sid;
		}

		return $code;
	}

	private static function _getQrcodeFile($sid, $orderType, $orderid)
	{
		$file = QRCODE_FILE_PATH . $orderType . '/' . $orderid . '/' . $sid . '.png';

		return $file;
	}

	private static function _getQrcodeUrl($sid, $orderType, $orderid)
	{
		$sufix = str_replace(ADMIN_HTDOCS_PATH, '', QRCODE_FILE_PATH);
		return 'http://' . ADMIN_IMG_HOST . '/' . $sufix . $orderType . '/' . $orderid . '/' . $sid . '.png';
	}
}