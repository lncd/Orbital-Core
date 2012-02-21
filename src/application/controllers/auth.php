<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Authentication
 *
 * Marshals sign-in request to the appropriate sign-in endpoint.
 *
 * @package  Orbital
 * @subpackage  Core
 * @author  Nick Jackson
 * @link  https://github.com/lncd/Orbital-Core
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
	 *
	 * @todo Validate Client ID and Redirect URI match
	 */

	function signin($endpoint)
	{

		// Make sure client_id and redirect_uri exist
		if ($this->input->get('client_id') && $this->input->get('redirect_uri'))
		{

			$this->load->library('authentication/Auth_' . $endpoint, '', 'auth_endpoint');
			$state->client_id = $this->input->get('client_id');
			$state->redirect_uri = $this->input->get('redirect_uri');
			
			if ($this->input->get('state'))
			{
				$state->state = $this->input->get('state');
			}
			
			if ($this->input->get('scope'))
			{
				$state->scope = $this->input->get('scope');
			}
			else
			{
				$state->scope = 'access';
			}
			
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
			if (isset($response->state) && isset($response->user_email) && isset($response->user_name))
			{

				// Unserialise the state
				$state = unserialize($response->state);

				// Fields present!

				// Test to see if user exists
				if (!$this->users->get_user($response->user_email))
				{
					// User does not exist, try to create!
					if (!$this->users->create_user($response->user_email, $response->user_name))
					{
						$this->load->view('error', array('message' => 'Unable to create user object.'));
						return;
					}
				}
				
				// Begin OAuth
				
				// Generate all-purpose redirect URI
				$redirect_uri =  $state->redirect_uri . '?code=' . $code;
					
				if (isset($state->state))
				{
					$redirect_uri .= '&state=' . $state->state;
				}
				
				// Generate code and perform redirect
				if ($code = $this->oauth->generate_code($state->client_id, $response->user_email, $state->scope))
				{
				
					$redirect_uri =  $state->redirect_uri . '?code=' . $code;
					
					if (isset($state->state))
					{
						$redirect_uri .= '&state=' . $state->state;
					}
				
					$this->output->set_header('Location: ' . $redirect_uri);
				}
				else
				{
					$this->output->set_header('Location: ' . $redirect_uri . '&error=server_error&error_description=Unable to generate code.');
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
			$this->load->view('error', array('message' => 'Unexpected or invalid response from sign-in library.'));
		}
	}
}

// EOF