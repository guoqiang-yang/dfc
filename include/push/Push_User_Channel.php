<?php

/**
 * 用户推送
 */
class Push_User_Channel extends Base_Func
{
	private $_dao = NULL;

	public function __construct()
	{
		$this->_dao = new Data_Dao('t_user_channel');

		parent::__construct();
	}

	public function getByUid($uid)
	{
		$where = array('uid' => $uid);
		$list = $this->_dao->order('mtime', 'desc')->limit(0, 1)->getListWhere($where);

		return array_shift($list);
	}

	public function getByUidDeviceToken($uid, $deviceToken)
	{
		$where = array('uid' => $uid, 'device_token' => $deviceToken);
		$list = $this->_dao->getListWhere($where);

		return array_shift($list);
	}

	public function getByUids($uids)
	{
		$data = array();
		$exUids = array();
		$where = array('uid' => $uids);

		$list = $this->_dao->order('mtime', 'desc')->getListWhere($where);
		if (!empty($list))
		{
			foreach ($list as $id => $info)
			{
				if (!in_array($info['uid'], $exUids))
				{
					$data[$id] = $info;

					$exUids[] = $info['uid'];
				}
			}
		}

		return $data;
	}

	public function add($info)
	{
		return $this->_dao->add($info);
	}

	public function update($id, $update)
	{
		return $this->_dao->update($id, $update);
	}

}
