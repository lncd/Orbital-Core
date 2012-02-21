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
	 * @parameter $endpoint The designated sign-in endpoint.
	 */

	function signin($endpoint)
	{
	
		// Make sure client_id and redirect_uri exist
		if ($this->input->get('client_id') && $this->input->get('redirect_uri'))
		{
	
			$this->load->library('authentication/Auth_' . $endpoint, '', 'auth_endpoint');
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
	 * @parameter $endpoint The designated sign-in endpoint.
	 */
	
	function callback($endpoint)
	{
		$this->load->library('authentication/Auth_' . $endpoint, '', 'auth_endpoint');
		if ($response = $this->auth_endpoint->callback())
		{
			$this->load->model('users');
			
			// Test to see if user exists
			if ($this->users->get_user($response->user_email))
			{
				echo $response->user_email . ' exists!';
			}
			else
			{
				echo $response->user_email . ' does not exist!';
			}
		}
		else
		{
			$this->load->view('error', array('message' => 'Unexpected response from sign-in service.'));
		}
	}
}

// EOF