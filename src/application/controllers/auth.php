<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Authentication
 *
 * Marshals sign-in request to the appropriate sign-in endpoint.
 *
 * @package		Orbital
 * @subpackage  Core
 * @author		Nick Jackson
 * @link		https://github.com/lncd/Orbital-Core
 */

class Auth extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Sign-In Marshalling
	 *
	 * Builds state variable and routes sign-in requests to the appropriate
	 * authentication library.
	 *
	 * @param string $endpoint The designated sign-in endpoint.
	 */

	function signin($endpoint)
	{
	
		// Make sure client_id and redirect_uri exist
		if ($this->input->get('client_id') && $this->input->get('redirect_uri'))
		{
	
			$state['client_id'] = $this->input->get('client_id');
			$state['redirect_uri'] = $this->input->get('redirect_uri');
			$this->auth_endpoint->signin($state);
			
		}
		else
		{
			$this->load->view('error', array('message' => 'Client ID or Redirect URI not present in sign-in request.'));
		}
	}
	
	/**
	 * Callback marshalling
	 *
	 * Routes sign-in callbacks to the appropriate authentication library,
	 * validates the user data, performs any necessary user creation, builds
	 * the OAuth response for the client, and redirects accordingly.
	 *
	 * @param string $endpoint The designated sign-in endpoint.
	 *
	 * @todo Rewrite this to use exceptions.
	 */
	
	function callback($endpoint)
	{
		$this->load->library('authentication/Auth_' . $endpoint, '', 'auth_endpoint');
		if ($response = $this->auth_endpoint->callback())
		{
			$this->load->model('users');
			
			// Ensure that all expected fields are present
			if (isset($response['state']) && isset($response['user_email']) && isset($response['user_name']))
			{
			
				// Unserialise the state
				$state = unserialize($response['state']);
			
				// Fields present!
			
				// Test to see if user exists
				if ($this->users->get_user($response->user_email))
				{
					echo $response->user_email . ' exists!';
				}
				else
				{
				
					// User does not exist, try to create!
					
					/**
					 * @todo Include RDF magic
					 */
					
					if (!$this->users->create_user($response['user_email'], $response['user_name']))
					{
						$this->load->view('error', array('message' => 'Unable to create user object.'));
						return;
					}
				}
				
			}
			else
			{
				// Required fields not present
				$this->load->view('error', array('message' => 'Required details not provided by sign-in library.'));
			}
			
		}
		else
		{
			// Sign-in library has returned FALSE, or nothing at all.
			$this->load->view('error', array('message' => 'Unexpected response from sign-in library.'));
		}
	}
}

// EOF