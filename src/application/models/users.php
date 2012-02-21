<?php defined('BASEPATH') or exit('No direct script access allowed');

class Users extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get User Details
	 *
	 * Returns entire user information object for given address.
	 *
	 * @param string $email Email address of user.
	 *
	 * @return object|false The user object, or FALSE if user does not exist.
	 */

	function get_user($email)
	{
		if ($user = $this->mongo_db->where(array('email' => $email))->get('users'))
		{
			if (count($user) == 1)
			{
				return $user[0];
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
	 * Create User
	 *
	 * Create a new user object with the given parameters.
	 *
	 * @param string $email Email address of user.
	 * @param string $name Name of user.
	 * @param string $rdf URI where an RDF description of the user may be found.
	 *
	 * @return bool TRUE if creation has succeeded, FALSE if not.
	 */
	
	function create_user($email, $name, $rdf = NULL)
	{
		if ($user = $this->mongo_db->where(array('email' => $email))->get('users'))
		{
			// Sanity check, does this user exist?
			if (count($user) == 1)
			{
				// User exists, throw a FALSE.
				return FALSE;
			}
			else
			{
				// User does not exist, carry on!
				
				/**
				 * @todo Email address validation.
				 */
				
				$insert = array(
					'email' => $email,
					'name' => $name
				);
				
				/**
				 * @todo URI validation.
				 */
				
				if ($rdf != NULL)
				{
					$insert['rdf'] = $rdf;
				}
				
				// Attempt insert
				
				if ($this->mongo_db->insert('users', $insert))
				{
					return TRUE;
				}
				else
				{
					return FALSE;
				}
			}
		}
		else
		{
			return FALSE;
		}
	}

}

// End of file users.php