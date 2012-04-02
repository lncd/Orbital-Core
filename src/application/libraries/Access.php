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
 *
 * @todo Rewrite to use exceptions.
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
	
		$headers = $this->_ci->input->request_headers();
	
		// Is there an Authorization header? If not, demand authentication.
		if ( ! isset($headers['Authorization']))
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
		
			// Ensure the header is vaguely sensible
			$authorisation_header = explode(' ', $headers['Authorization']);
			
			if (count($authorisation_header) === 2 && $authorisation_header[0] === 'Bearer')
			{
				// Looks the right length, has the right auth type - see if the token is valid.
				if ($user = $this->_ci->oauth->validate_token(base64_decode($authorisation_header[1])))
				{
				
					// Token is valid, hooray! But does it have the scopes?
					if ($this->_ci->oauth->validate_scopes(base64_decode($authorisation_header[1]), $scopes))
					{
						return $user;
					}
					else
					{
						$this->_ci->output
						->set_status_header('403')
						->set_output(json_encode(array(
							'error' => 'insufficient_scope',
							'error_description' => 'The access token provided does not have sufficient scopes to perform this action.',
							'scope' => implode(' ', $scopes)
						)));
						return FALSE;
					}
				}
				else
				{
				
					$this->_ci->output
						->set_status_header('401')
						->set_header('WWW-Authenticate: Bearer realm="Orbital Core: User", error="invalid_token", error_description="The access token is invalid, has expired or has been revoked."');
					return FALSE;
				}
			}
			else
			{
			
				// Demand authentication
				$this->_ci->output
					->set_status_header('401')
					->set_header('WWW-Authenticate: Bearer realm="Orbital Core: User"');
				// Nothing else can or should happen at this point. Wrap it up.
				return FALSE;
			}
		
		}
	
	}
	
	/**
	 * User Has Permission
	 *
	 * Does the specified user have the specified permission?
	 *
	 * @access public
	 *
	 * @param string $user       Email address of the user to test against.
	 * @param string $aspect     Aspect to test for permission.
	 * @param mixed  $value      Value to see if present.
	 * @param string $identifier Identifier to test for permission against.
	 *
	 * @return bool TRUE if the user has the aspect, FALSE if not.
	 */
	 
	function user_has_permission($user, $aspect, $value = TRUE, $identifier = NULL)
	{
	
		$permission_query = array('user' => $user, 'aspect' => $aspect, 'values' => $value);
		
		if ($identifier !== NULL)
		{
			$permission_query['identifier'] = $identifier;
		}
	
		if ($this->_ci->mongo_db->where()->get('permissions'))
		{
			return TRUE;
		}
		else
		{
			$this->_ci->output
				->set_status_header('403')
				->set_output(json_encode(array(
					'error' => 'no_permission',
					'error_description' => 'The current user does not have permission to perform this action.'
				)));
			return FALSE;
		}
	}

}

// End of file Access.php
// Location: ./libraries/Access.php