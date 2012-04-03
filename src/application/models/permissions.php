<?php defined('BASEPATH') or exit('No direct script access allowed');

class Permissions extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get Permissions
	 *
	 * Returns projects that user has access to.
	 *
	 * @return identifiers The list of projects that the user has access to.
	 */

	function get_permissions_with_value($user, $aspect, $value)
	{
		if ($permissions = $this->mongo_db->where(array('user' => $user, 'aspect' => $aspect, 'values' => $value))->get('permissions'))
		{
			if (count($permissions) > 0)
			{
				$output = array();
				foreach($permissions as $permission)
				{
					$output[] = $permission['identifier'];
				}
				return $output;
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

// End of file permissions.php