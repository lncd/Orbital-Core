<?php defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}

	function get_user($email)
	{
		if ($user = $this->mongo_db->where(array('email' => $email))->get('users'))
		{
			if (count($user) == 1)
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}

}

// End of file users.php