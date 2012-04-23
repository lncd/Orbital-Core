<?php defined('BASEPATH') OR exit('No direct script access allowed');


/**
 * Users
 *
 * Finds and creates users.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @link       https://github.com/lncd/Orbital-Core
 */

class Users_model extends CI_Model {


	/**
	 * construct
	 */

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
		if ($user = $this->db->where('user_email', $email)->get('users'))
		{
			if ($user->num_rows() === 1)
			{
				$user = $user->row();
				
				return array(
					'email' => $user->user_email,
					'name' => $user->user_name,
					'institution' => $user->user_institution
				);
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
	 * @param string $name  Name of user.
	 * @param string $rdf URI where an RDF description of the user may be found.
	 *
	 * @return bool TRUE if creation has succeeded, FALSE if not.
	 */
	
	function create_user($email, $name, $uri = NULL, $institution = NULL)
	{
		if ($this->get_user($email))
		{
			// User exists, throw a FALSE.
			return FALSE;
		}
		else
		{
			// User does not exist, carry on!
			
			/**
			 * @todo Email address validation.
			 * @todo URI validation.
			 */
			
			$insert = array(
				'user_email' => $email,
				'user_name' => $name,
				'user_uri' => $uri,
				'user_institution' => $institution
			);
			
			// Attempt insert
			
			if ($this->db->insert('users', $insert))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
	}
}

// End of file users.php