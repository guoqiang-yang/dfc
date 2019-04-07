<?php

/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 16/4/25
 * Time: 15:09
 */
require_once INCLUDE_PATH . '/push/sdk.php';

class Push_Api extends Base_Api
{
	public static function saveChannelId($uid, $deviceToken, $deviceType, $channelId, $userId)
	{
		$puc = new Push_User_Channel();

		$info = $puc->getByUidDeviceToken($uid, $deviceToken);
		if (empty($info))
		{
			$info = array(
				'uid' => $uid,
				'device_type' => Conf_Base::$DEVICE_TYPE[$deviceType] ? Conf_Base::$DEVICE_TYPE[$deviceType] : 3,   //默认安卓
				'device_token' => $deviceToken,
				'channel_id' => $channelId,
				'user_id' => $userId,
			);

			$puc->add($info);
		}
		else if ($info['channel_id'] != $channelId)
		{
			$puc->update($info['id'], array('channel_id' => $channelId));
		}
		else if ($info['user_id'] != $userId)
		{
			$puc->update($info['id'], array('user_id' => $userId));
		}
	}

	public static function pushToUser($uid, $description, $title = '', $link = '', $type = Conf_Base::TYPE_NOTICE)
	{
		$puc = new Push_User_Channel();
		$info = $puc->getByUid($uid);
		if (!empty($info) && !empty($info['channel_id']))
		{
			self::_pushMsg($info['channel_id'], $info['user_id'], $info['device_type'], $description, $title, $link, $type);
		}
	}

	public static function pushToUserByCid($cid, $description, $title = '', $link = '', $type = Conf_Base::TYPE_NOTICE)
	{
		$users = Crm2_Api::getCustomerInfo($cid, true, false);

		if (!empty($users['users']))
		{
			$uids = Tool_Array::getFields($users['users'], 'uid');

			$puc = new Push_User_Channel();
			$list = $puc->getByUids($uids);
			if (!empty($list))
			{
				foreach ($list as $item)
				{
					if (empty($item) || empty($item['channel_id']))
					{
						continue;
					}

					self::_pushMsg($item['channel_id'], $item['user_id'], $item['device_type'], $description, $title, $link, $type);
				}
			}
		}
	}

	private static function _pushMsg($channelId, $userId, $deviceType, $description, $title, $link, $type)
	{
		$sdk = new PushSDK();

		$message = array (
			// 消息的标题.
			'description' => $description,
		);
		if (!empty($title))
		{
			$message['title'] = $title;
		}
		if (!empty($link))
		{
			$message['custom_content'] = array('href' => $link);
		}

		$opts = array(
			'msg_type' => $type,
			'device_type' => $deviceType,
			'user_id' => $userId,
		);

		$rs = $sdk->pushMsgToSingleDevice($channelId, $message, $opts);
		// 判断返回值,当发送失败时, $rs的结果为false, 可以通过getError来获得错误信息.
		if($rs === false)
		{
			$desc = 'errorcode=' . $sdk->getLastErrorCode() . "\terrormsg=" . $sdk->getLastErrorMsg();
			Tool_Log::addFileLog('/baidu_push/push_err_' . date('Ymd'), $desc);
		}
		else
		{
			Tool_Log::addFileLog('/baidu_push/push_succ_' . date('Ymd'), '');
		}
	}
}