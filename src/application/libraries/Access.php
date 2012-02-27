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
	
	private $_ci;
	
	function __construct()
	{
		$this->_ci =& get_instance();
	}

	/**
	 * Valid Application
	 *
	 * Ensures that there is a valid application present.
	 *
	 * @access public
	 *
	 * @return string|FALSE Client ID if credentials match, FALSE if not.
	 */

	function valid_application()
	{
	
		// Is a user provided? If not, authenticate them!
		if ( ! $this->_ci->input->server('PHP_AUTH_USER'))
		{
			// Demand authentication
			$this->_ci->output
				->set_status_header('401')
				->set_header('WWW-Authenticate: Basic realm="Orbital Core: Application"');
			// Nothing else can or should happen at this point. Wrap it up.
			return FALSE;
		}
		else
		{
			// Check to see if credentials are valid.
			
			if ($this->_ci->oauth->validate_app_credentials($this->_ci->input->server('PHP_AUTH_USER'), NULL, $this->_ci->input->server('PHP_AUTH_PW')))
			{
				return $this->_ci->input->server('PHP_AUTH_USER');
			}
			else
			{
				$this->_ci->output
					->set_status_header('401')
					->set_header('WWW-Authenticate: Basic realm="Orbital Core: Application"');
				return FALSE;
			}
		}
	
	}
	
	/**
	 * Valid User
	 *
	 * Ensures that there is a valid user present for the given scope.
	 *
	 * @access public
	 *
	 * @param array $scopes Scopes to ensure that the user has access to.
	 *
	 * @return string|FALSE User's email address if credentials match, FALSE
	 *                      if not.
	 */

	function valid_user($scopes)
	{
	
		echo $this->_ci->input->server('Authorization');
		die();
	
		/*
	
		// Is there a present HTTP user? If not, demand authentication.
		if ( ! $this->_ci->input->server('PHP_AUTH_USER'))
		{
			// Demand authentication
			$this->_ci->output
				->set_status_header('401')
				->set_header('WWW-Authenticate: Bearer realm="Orbital Core: User"');
			// Nothing else can or should happen at this point. Wrap it up.
			return FALSE;
		}
		else
		{
			// Check to see if credentials are valid.
			
			if ($this->_ci->oauth->validate_app_credentials($this->_ci->input->server('PHP_AUTH_USER'), NULL, $this->_ci->input->server('PHP_AUTH_PW')))
			{
				return $this->_ci->input->server('PHP_AUTH_USER');
			}
			else
			{
				return FALSE;
			}
		}
		
		*/
	
	}

}

// End of file Access.php
// Location: ./libraries/Access.php