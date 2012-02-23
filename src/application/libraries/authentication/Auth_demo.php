<?php

/**
 * Authentication Demo
 *
 * Demonstration of response for authentication.
 *
 * @package     Orbital
 * @subpackage  Authentication
 * @author      Nick Jackson
 * @copyright   2012 University of Lincoln
 * @link        https://github.com/lncd/Orbital-Core
 */

class Auth_demo {

	private $_ci;
	
	function __construct()
	{
		$this->_ci =& get_instance();
	}

	function signin($state)
	{
	
		echo '<h1>Sign In Demo</h1>';
		
		echo '<p><a href="https://core.orbital.lncd.org/auth/callback/demo?state=' . urlencode(serialize($state)) . '">Sign in as orbital-demo@lncd.org</a></p>';
	
	}
	
	function callback()
	{
	
		$return->state = $this->_ci->input->get('state');
		$return->user_email = 'orbital-demo@lncd.org';
		$return->user_name = 'Orbital Demo User';
		
		return $return;
	}

}

// End of file Auth_demo.php