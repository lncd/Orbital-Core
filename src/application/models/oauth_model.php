<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * OAuth Model
 *
 * Provides interaction with the OAuth database collections.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
*/

class Oauth_model extends CI_Model {

	/**
	 * Constructor
	 */

	function __construct()
	{
		parent::__construct();
	}

	function get_handlers()
	{
	
		if ($handlers = $this->db->order_by('handler_name')->get('oauth_handlers'))
		{
		
			if ($handlers->num_rows() > 0)
			{
				$output = array();
				foreach ($handlers->result() as $handler)
				{
					$output[] = array(
						'name' => $handler->handler_name,
						'tag' => $handler->handler_tag
					);
				}
				return $output;
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
	 * Validate Token
	 *
	 * Tests to see if provided token is valid and has not expired.
	 *
	 * @param string $access_token Redirect URI to be tested.
	 *
	 * @return string|FALSE Email address of the user if token is valid, FALSE
	 *                      if not.
	 */

	function validate_token($access_token)
	{
	
		$token_db = $this->db
			->where('at_token', $access_token)
			->where('at_expires >', date('Y-m-d H:i:s'), time())
			->get('oauth_access_tokens');
	
		if ($token_db->num_rows() === 1)
		{
		
			$token = $token_db->row();
		
			return $token->at_user;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	 * Validate Scopes
	 *
	 * See if a token is valid for the given scopes.
	 *
	 * @param string $access_token Redirect URI to be tested.
	 * @param array  $scopes       Scopes to ensure this token has.
	 *
	 * @return bool TRUE if token has all scopes, FALSE if not.
	 */

	function validate_scopes($access_token, $scopes)
	{
		
		foreach ($scopes as $scope)
		{
			$scope = $this->db
				->where('sa_at', $access_token)
				->where('sa_scope', $scope)
				->get('oauth_at_scopes');
				
			if ($scope->num_rows() !== 1)
			{
				return FALSE;
			}
		}
	
		return TRUE;
	}


	/**
	 * Validate Application Credentials
	 *
	 * Tests to see if provided application credentials are valid. Must
	 * include Client ID, AND redirect URI OR client secret OR both.
	 *
	 * @access public
	 *
	 * @param string $client_id      Client ID to be tested.
	 * @param string $redirect_uri   Redirect URI to be tested.
	 * @param string $client_secret  Client secret to be tested.
	 *
	 * @return bool TRUE if credentials match, FALSE if not.
	 */

	function validate_app_credentials($client_id, $redirect_uri = NULL, $client_secret = NULL)
	{
	
		// Ensure we have either redirect URI or client secret.
		if ($redirect_uri === NULL && $client_secret === NULL)
		{
			return FALSE;
		}

		$credentials['app_id'] = $client_id;

		// Only check URI if it's provided
		if ($redirect_uri !== NULL)
		{
			$credentials['app_redirect'] = $redirect_uri;
		}

		// Only check secret if it's provided
		if ($client_secret !== NULL)
		{
			$credentials['app_secret'] = $client_secret;
		}

		if ($application = $this->db->where($credentials)->get('applications'))
		{
			if ($application->num_rows() === 1)
			{
				return TRUE;
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
	 * Create Code
	 *
	 * Generates a code for the given client_id, which can be used in a token
	 * swap.
	 *
	 * @param string $client_id  Client ID for which this code is valid.
	 * @param string $user       User for which this code is valid
	 * @param array $scopes      Requested scopes for this token
	 *
	 * @return string|FALSE The code which should be passed to the client, or
	 *                      FALSE if it cannot be generated.
	 *
	 * @todo Ensure that scopes requested are valid
	 */

	function generate_code($client_id, $user, $scopes = 'access')
	{
	
		$code = $this->db->where('code_client', $client_id)->where('code_user', $user)->get('oauth_codes');
	
		if ($code->num_rows() === 1)
		{
			// An existing code exists for this client/user combination. Destroy it with fire.
			$this->db->where('code_client', $client_id)->where('code_user', $user)->delete('oauth_codes');
		}

		// Generate a new code
		$insert = array(
			'code_code' => random_string('alnum', 32),
			'code_client' => $client_id,
			'code_user' => $user,
			'code_expires' => date('Y-m-d H:i:s', time() + 60), // Codes are only valid for 60 seconds.
		);

		// If code is OK, load up some scopes and return it. If not, return false.
		if ($this->db->insert('oauth_codes', $insert))
		{
		
			foreach (explode(' ', urldecode($scopes)) as $scope)
			{
				if ( ! $this->db->insert('oauth_code_scopes', array('sc_scope' => $scope, 'sc_code' => $insert['code_code'])))
				{
					return FALSE;
				}
			}
		
			return $insert['code_code'];
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Swap Code for Access Token
	 *
	 * Generates an access token and refresh token for the given code
	 *
	 * @param string $code       Code to perform the swap for.
	 * @param string $client_id  Client ID for this token.
	 *
	 * @return object|FALSE An object containing the tokens, or FALSE if the
	 *                      swap fails for any reason.
	 */

	function swap_code($code, $client_id)
	{

		$code_db = $this->db
			->where('code_code', $code)
			->where('code_client', $client_id)
			->where('code_expires >', date('Y-m-d H:i:s', time()))
			->get('oauth_codes');

		// Ensure this code exists and is valid.
		if ($code_db->num_rows() === 1)
		{
			// Grab the code for scopes and user
			$code_data = $code_db->row();

			// Grab scopes
			$scopes_db = $this->db
				->where('sc_code', $code)
				->get('oauth_code_scopes');
				
			$scopes = array();
			
			foreach ($scopes_db->result() as $scope)
			{
				$scopes[] = $scope->sc_scope;
			}

			// Remove the code.
			$this->db
				->where('code_client', $client_id)
				->where('code_code', $code)
				->delete('oauth_codes');

			// Remove existing AT/RT pair for this client and user.
			$this->db
				->where('at_client', $client_id)
				->where('at_user', $code_data->code_user)
				->delete('oauth_access_tokens');

			// Generate new AT and RT

			$access_token = random_string('alnum', 64);
			$refresh_token = random_string('alnum', 64);
			$expires_in = 21600;

			$insert = array(
				'at_token' => $access_token,
				'at_refresh' => $refresh_token,
				'at_user' => $code_data->code_user,
				'at_client' => $client_id,
				'at_expires' => date('Y-m-d H:i:s', time() + $expires_in)
			);

			if ($this->db->insert('oauth_access_tokens', $insert))
			{
			
				// Access token created, spin scopes
				
				foreach ($scopes as $scope)
				{
					if ( ! $this->db->insert('oauth_at_scopes', array('sa_scope' => $scope, 'sa_at' => $access_token)))
					{
						return FALSE;
					}
				}
			
				return array(
					'access_token' => $access_token,
					'refresh_token' => $refresh_token,
					'scope' => $scopes,
					'expires_in' => $expires_in,
					'user' => $code_data->code_user
				);
			}
			else
			{
				return FALSE;
			}

		}
		else
		{
			// This code doesn't exist or is invalid.
			return FALSE;
		}

	}
	
	/**
	 * Swap Refresh Token for new Access Token
	 *
	 * Generates a new access token and refresh token for the given refresh
	 * token.
	 *
	 * @param string $refresh_token Code to perform the swap for.
	 * @param string $client_id     Client ID for this token.
	 *
	 * @return object|FALSE An object containing the tokens, or FALSE if the
	 *                      swap fails for any reason.
	 */

	function swap_refresh_token($refresh_token, $client_id)
	{

		$token = $this->db
			->where('at_refresh', $refresh_token)
			->where('at_client', $client_id)
			->get('oauth_access_tokens');

		// Ensure this token exists.
		if ($token->num_rows() === 1)
		{
		
			$token_data = $token->row();
			
			// Grab scopes
			// Grab scopes
			$scopes_db = $this->db
				->where('sa_at', $token_data->at_token)
				->get('oauth_at_scopes');
				
			$scopes = array();
			
			foreach ($scopes_db->result() as $scope)
			{
				$scopes[] = $scope->sa_scope;
			}

			// Generate new AT and RT

			$new_access_token = random_string('alnum', 64);
			$new_refresh_token = random_string('alnum', 64);
			$expires_in = 21600;

			$update = array(
				'at_token' => $new_access_token,
				'at_refresh' => $new_refresh_token,
				'at_expires' => date('Y-m-d H:i:s', time() + $expires_in)
			);

			if ($this->db
				->where('at_refresh', $refresh_token)
				->where('at_client', $client_id)
				->update('oauth_access_tokens', $update))
			{
				return array(
					'access_token' => $new_access_token,
					'refresh_token' => $new_refresh_token,
					'scope' => $scopes,
					'expires_in' => $expires_in,
					'user' => $token_data->at_user
				);
			}
			else
			{
				return FALSE;
			}

		}
		else
		{
			// This token doesn't exist or is invalid.
			return FALSE;
		}

	}

}

// End of file oauth.php
// Location: ./models/oauth.php