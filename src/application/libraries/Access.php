<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Access Control Library
 *
 * Access control for Orbital Core.
 *
 * @category   Library
 * @package    Orbital
 * @subpackage Core
 * @autho      Nick Jackson <nijackson@lincoln.ac.uk>
 * @link       https://github.com/lncd/Orbital-Core
*/

class Access {
	
	function __construct()
	{
	}

	/**
	 * Tests to ensure that application authentication is correct.
	 *
	 * @access public
	 */

	function valid_application()
	{
	
		// Is there a present HTTP user? If not, demand authentication.
		if (!$this->input->server('PHP_AUTH_USER'))
		{
			header('HTTP/1.0 401 Unauthorized');
			header('WWW-Authenticate: Basic realm="Orbital Core"');
			// Nothing else can possibly happen at this point. Wrap it up.
			return FALSE;
		}
		else
		{
			// Check to see if credentials are valid.
			
			if ($this->oauth->validate_app_credentials($this->input->server('PHP_AUTH_USER'), NULL, $this->input->server('PHP_AUTH_PW')))
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

// End of file Access.php
// Location: ./libraries/Access.php