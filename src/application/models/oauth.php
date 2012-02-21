<?php defined('BASEPATH') or exit('No direct script access allowed');

class OAuth extends CI_Model {

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Create Code
	 *
	 * Generates a code for the given client_id, which can be used in a token
	 * swap.
	 *
	 * @param string $client_id Client ID for which this code is valid.
	 * @param string $user User for which this code is valid
	 * @param array $scopes Requested scopes for this token
	 *
	 * @return string|false The code which should be passed to the client, or
	 *                      FALSE if it cannot be generated.
	 */

	function generate_code($client_id, $user, $scopes = array('access'))
	{
		if ($user = $this->mongo_db->where(array('client_id' => $client_id, 'user' => $user))->get('oauth_codes'))
		{
			// An existing code exists for this client/user combination. Destroy it with fire.
			$this->mongo_db>where(array('client_id' => $client_id, 'user' => $user))->remove('oauth_codes');
		}
		
		// Generate a new code
		$insert = array(
			'code' => uniqid(random_string('alnum', TRUE)),
			'client_id' => $client_id,
			'user' => $user,
			'expires' => time() + 300
		);
		
		if ($this->mongo_db->insert('users', $insert))
		{
			return $insert['code'];
		}
		else
		{
			return FALSE;
		}
	}

}

// End of file users.php