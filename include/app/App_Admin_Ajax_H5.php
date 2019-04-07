<?php
class App_Admin_Ajax_H5 extends App_Admin_Ajax
{
	function __construct($lgmode)
	{
		parent::__construct($lgmode, ADMIN_TEMPLATE_H5_PATH, ADMIN_HOST_H5);
	}
}
