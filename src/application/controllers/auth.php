<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * Authentication
 *
 * Marshals sign-in request to the appropriate sign-in endpoint.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @link       https://github.com/lncd/Orbital-Core
 */

class Auth extends Orbital_Controller {

	/**
	 * Constructor
	 */

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

	function signin_get($endpoint)
	{

		// Make sure client_id and redirect_uri exist
		if ($this->input->get('response_type')
			AND $this->input->get('response_type') === 'code'
			AND $this->input->get('client_id')
			AND $this->input->get('redirect_uri'))
		{

			if ($this->oauth->validate_app_credentials($this->input->get('client_id'), $this->input->get('redirect_uri')))
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
				$this->output->set_status_header('400');
				$this->load->view('error', array('message' => 'Client ID or redirect URI are not recognised or are not valid.'));
			}

		}
		else
		{
			$this->output->set_status_header('400');
			$this->load->view('error', array('message' => 'Client ID, redirect URI or response type not present in sign-in request.'));
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

	function callback_get($endpoint)
	{
		$this->load->library('authentication/Auth_' . $endpoint, '', 'auth_endpoint');
		if ($response = $this->auth_endpoint->callback())
		{
			$this->load->model('users');

			// Ensure that all expected fields are present
			if (isset($response->state) AND isset($response->user_email) AND isset($response->user_name))
			{

				// Unserialise the state
				$state = unserialize(urldecode($response->state));

				// Fields present!

				// Test to see if user exists
				if ( ! $this->users->get_user($response->user_email))
				{
					// User does not exist, try to create!
					if ( ! $this->users->create_user($response->user_email, $response->user_name, $response->rdf, $response->institution))
					{
						$this->output->set_status_header('500');						
						$redirect_uri =  $state->redirect_uri . '?error=server_error&error_description=Unable to create user object.';

						if (isset($state->state))
						{
							$redirect_uri .= '&state=' . $state->state;
						}
	
						$this->output->set_header('Location: ' . $redirect_uri);
						return;
					}
				}

				// Begin OAuth

				// Generate code and perform redirect
				if ($code = $this->oauth->generate_code($state->client_id, $response->user_email, $state->scope))
				{

					$redirect_uri = $state->redirect_uri . '?code=' . $code;

					if (isset($state->state))
					{
						$redirect_uri .= '&state=' . $state->state;
					}

					$this->output->set_header('Location: ' . $redirect_uri);
				}
				else
				{

					$redirect_uri =  $state->redirect_uri . '?error=server_error&error_description=Unable to generate code';

					if (isset($state->state))
					{
						$redirect_uri .= '&state=' . $state->state;
					}

					$this->output->set_header('Location: ' . $redirect_uri);
				}

			}
			else
			{
				// Required fields not present
				$this->output->set_status_header('500');
				$this->load->view('error', array('message' => 'Required details not provided by sign-in library.'));
			}

		}
		else
		{

			// Sign-in library has returned FALSE, or nothing at all.
			$this->output->set_status_header('500');
			$this->load->view('error', array('message' => 'Unexpected or invalid response from sign-in library.'));
		}
	}

	/**
	 * Access Token Swap
	 *
	 * Swaps a code for an access token and refresh token.
	 */

	function access_token_post()
	{
	
		if ($application = $this->access->valid_application())
		{
	

			if ($this->post('grant_type')
				AND $this->post('grant_type') === 'authorization_code'
				AND $this->post('code'))			{
				
				// Client credentials valid, try perform swap
				if ($tokens = $this->oauth->swap_code($this->input->post('code'), $application))
				{
			
					$this->output
						->set_content_type('application/json')
						->set_output(json_encode(array(
							'access_token' => $tokens['access_token'],
							'token_type' => 'bearer',
							'expires_in' => $tokens['expires_in'],
							'refresh_token' => $tokens['refresh_token'],
							'scope' => implode(' ', $tokens['scope']),
							'user' => $tokens['user'],
							'system_admin' => $this->access->user_has_permission($tokens['user'], 'system_admin')
						)));
						
				}
				else
				{
				
					// Code swap failed. Probably invalid.
				
					$this->output
						->set_content_type('application/json')
						->set_status_header('400')
						->set_output(json_encode(array(
							'error' => 'invalid_grant',
							'error_description' => 'The provided code is not valid for these credentials, has already been used, or has expired.'
						)));
				}
	
			}
			else
			{
				// Something is missing. Abort!
				$this->output
					->set_content_type('application/json')
					->set_status_header('400')
					->set_output(json_encode(array(
						'error' => 'invalid_request',
						'error_description' => 'The request did not include all required elements.'
					)));
			}
		}
		else
		{
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(array(
					'error' => 'access_denied',
					'invalid_client' => 'The provided credentials did not match those expected.'
				)));
		}
	}
	
	/**
	 * Refresh Token Swap
	 *
	 * Swaps a refresh token for a new access token and refresh token.
	 */

	function refresh_token_post()
	{
		if ($application = $this->access->valid_application())
		{
<<<<<<< HEAD
			if ($this->input->post('grant_type')
				&& $this->input->post('grant_type') === 'refresh_token'
				&& $this->input->post('refresh_token'))
=======
			if ($this->post('grant_type')
				AND $this->post('grant_type') === 'refresh_token'
				AND $this->post('refresh_token'))
>>>>>>> More check style updates
			{
				
				// Client credentials valid, try perform swap
				if ($tokens = $this->oauth->swap_refresh_token($this->input->post('refresh_token'), $application))
				{
			
					$this->output
						->set_content_type('application/json')
						->set_output(json_encode(array(
							'access_token' => $tokens['access_token'],
							'token_type' => 'bearer',
							'expires_in' => $tokens['expires_in'],
							'refresh_token' => $tokens['refresh_token'],
							'scope' => implode(' ', $tokens['scope']),
							'user' => $tokens['user'],
							'system_admin' => $this->access->user_has_permission_aspect($tokens['user'], 'system_admin')
						)));
						
				}
				else
				{
				
					// Token swap failed. Probably invalid.
				
					$this->output
						->set_content_type('application/json')
						->set_status_header('400')
						->set_output(json_encode(array(
							'error' => 'invalid_grant',
							'error_description' => 'The provided refresh token is not valid for these credentials or has already been used.'
						)));
				}
	
			}
			else
			{
				// Something is missing. Abort!
				$this->output
					->set_content_type('application/json')
					->set_status_header('400')
					->set_output(json_encode(array(
						'error' => 'invalid_request',
						'error_description' => 'The request did not include all required elements.'
					)));
			}
		}
		else
		{
			$this->output
				->set_content_type('application/json')
				->set_output(json_encode(array(
					'error' => 'access_denied',
					'invalid_client' => 'The provided credentials did not match those expected.'
				)));
		}
	}
}

// End of file auth.php
// Location: ./controllers/auth.php