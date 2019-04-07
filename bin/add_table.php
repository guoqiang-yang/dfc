<?php

/**
 * 添加新表配置
 */

include_once('../global.php');

class App extends App_Cli
{
	private $table;
    private $dbname;

    private static $_db;

	protected function getPara()
	{
		if ($_SERVER['argc'] < 2)
		{
			$this->_trace('[Usage:] php ./%s table [database]',
				basename(__FILE__, '.php.'));
			exit;
		}

		$this->table = $_SERVER['argv'][1];
        $this->dbname = ($_SERVER['argc'] < 3) ? 'haocai':$_SERVER['argv'][2];
	}

	protected function main()
	{
        $sql = sprintf("insert into kind_setting(kind, table_num, table_prefix, id_field, version, remark)".
            "values('%s', 1, '%s', 'id', 1, '') ON DUPLICATE KEY UPDATE table_num=1",
            $this->table, $this->table);
        $res = $this->sQuery($sql);
        printf("kind_setting. affectedrows=%d\n", $res['affectedrows']);

        $sql = sprintf("insert into table_setting(kind,no,sid,db_name)".
            "values ('%s', 0, 1, '%s') ON DUPLICATE KEY UPDATE sid=1",
            $this->table, $this->dbname);
        $res = $this->sQuery($sql);
        printf("table_setting. affectedrows=%d\n", $res['affectedrows']);
	}

    private function _getConnection()
    {
        if (empty(self::$_db))
        {
            list($host, $user, $pass) = array(DB_HOST, 'haocai_dev', 'f4sqe7wKzDT3eQpYkCZdMfd6O');
            $database = 'dev_config';

            self::$_db = new mysqli($host, $user, $pass, $database);
            if (!self::$_db)
            {
                throw new Exception("Could not connect \'$host\' - " . mysqli_connect_error() );
            }
            if (!self::$_db->query("set names 'utf8';"))//TODO:	用配置取代
            {
                throw new Exception("Set Names 'utf8' Error	- "	.
                    self::$_db->errno .":". self::$_db->error, self::$_db->errno );
            }
        }
        return self::$_db;
    }

    public function sQuery($sql)
    {
        $db = self::_getConnection();
        $res = $db->query($sql);
        if (false === $res)
        {
            throw new Exception("excute '$sql' error - " . $db->errno .":". $db->error, $db->errno );
        }

        $data = array();
        $rownum = 0;
        if (is_object($res)	&& $res->num_rows != 0)
        {
            while ($row = $res->fetch_array(MYSQLI_ASSOC))
            {
                $data[] = $row;
            }
            $rownum = $res->num_rows;
        }

        return new Data_Result($data, $rownum, $db->insert_id, $db->affected_rows);
    }
}

$app = new App();
$app->run();