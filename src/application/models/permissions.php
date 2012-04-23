<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Permissions
 *
 * Finds and creates users permissions.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
*/

class Permissions extends CI_Model {

	/**
	 * construct
	 */

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get Permissions with value
	 *
	 * Returns projects that user has access to.
	 *
	 * $aspect what the permission is for
	 * $user   user whose permissions will be retrieved
	 * $value  what the user can do
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

	
	/**
	 * Get Permissions for identifier
	 *
	 * Returns permissions that user has access to a project.
	 *
	 * $user       user whose permissions will be retrieved
	 * $aspect     what the permission is for
	 * identifier  the project the the permissions are being retrieved for
	 * @return identifiers The list of permissions that the user has for teh project.
	 */

	function get_permissions_for_identifier($user, $aspect, $identifier)
	{
		if ($permissions = $this->mongo_db->where(array('user' => $user, 'aspect' => $aspect, 'identifier' => $identifier))->get('permissions'))
		{
			if (count($permissions) > 0)
			{
				$output = array();
				foreach($permissions as $permission)
				{
					$output = array_merge($output, $permission['values']);
				}
				return array_unique($output);
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


	/**
	 * Get users for identifier
	 *
	 * Returns users with permissions of an item.
	 *
	 * $aspect     the type of item
	 * $identifier the item that the users are found for
	 * @return users The list of users that the item has.
	 */

	function get_users_for_identifier($aspect, $identifier)
	{
		if ($users = $this->mongo_db->where(array('aspect' => $aspect, 'identifier' => $identifier))->get('permissions'))
		{
			if (count($users) > 0)
			{
				$output = array();
				foreach($users as $user)
				{
					if ( ! isset($output[$user['user']]))
					{
						$output[$user['user']] = array();
					}
					$output[$user['user']] = array_merge($output[$user['user']], $user['values']);
				}
				return array_unique($output);
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

	/**
	 * Create permission
	 *
	 * Returns identifier with permissions of an item.
	 *
	 * $aspect     the type of item
	 * $identifier the item that the users are found for
	 * @return users The list of users that the item has.
	 */

	function create_permission($user, $aspect, $values = NULL, $identifier = NULL)
	{
		$insert = array(
			'user' => $user,
			'aspect' => $aspect,
			'permission_created' => time()
		);

		if ($values !== NULL)
		{
			$insert['values'] = $values;
		}
		if ($identifier !== NULL)
		{
			$insert['identifier'] = $identifier;
		}
		// Attempt insert

		if ($this->mongo_db->insert('permissions', $insert))
		{
			return $identifier;
		}
		else
		{
			return FALSE;
		}
	}
}

// End of file permissions.php
// Location: ./models/permissions.php
