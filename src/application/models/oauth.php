<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * OAuth Model
 *
 * Provides interaction with the OAuth database collections.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @link       https://github.com/lncd/Orbital-Core
 */

class Oauth extends CI_Model {

	/**
	 * Constructor
	 */

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Validate Token
	 *
	 * Tests to see if provided token and client ID are valid, token has not
	 * expired, and scopes are valid.
	 *
	 * @param string $client_id    Client ID to be tested.
	 * @param string $access_token Redirect URI to be tested.
	 * @param array  $scopes       Scopes to ensure this token has.
	 *
	 * @return string|bool Email address of the user if token is valid, FALSE
	 *                     if not.
	 */

	function validate_token($client_id, $access_token, $scopes)
	{
	
		$this->mongo_db->where(array(
			'client_id' => $client_id,
			'access_token' => $access_token
		))->where_gt('expires', time());
	
		// Add the tokens to the evaluation
		foreach ($tokens as $token)
		{
			$this->mongo_db->where(array('scopes' => $token));
		}
	
		if ($token = $this->mongo_db->get('oauth_access_tokens'))
		{
			if (count($token) === 1)
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
	 * Validate Application Credentials
	 *
	 * Tests to see if provided application credentials are valid. Must
	 * include Client ID, AND redirect URI OR client secret OR both.
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

		$credentials['client_id'] = $client_id;

		// Only check URI if it's provided
		if ($redirect_uri !== NULL)
		{
			$credentials['endpoint'] = $redirect_uri;
		}

		// Only check secret if it's provided
		if ($client_secret !== NULL)
		{
			$credentials['app_secret'] = $client_secret;
		}

		if ($application = $this->mongo_db->where($credentials)->get('applications'))
		{
			if (count($application) === 1)
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
	 * @return string|false The code which should be passed to the client, or
	 *                      FALSE if it cannot be generated.
	 *
	 * @todo Ensure that scopes requested are valid
	 */

	function generate_code($client_id, $user, $scopes = 'access')
	{
		if ($codes = $this->mongo_db->where(array('client_id' => $client_id, 'user' => $user))->get('oauth_codes'))
		{
			// An existing code exists for this client/user combination. Destroy it with fire.
			$this->mongo_db->where(array('client_id' => $client_id, 'user' => $user))->delete('oauth_codes');
		}

		// Generate a new code
		$insert = array(
			'code' => random_string('alnum', 32),
			'client_id' => $client_id,
			'user' => $user,
			'expires' => time() + 60, // Codes are only valid for 60 seconds.
			'scopes' => explode(' ', $scopes)
		);

		// If code is OK, return it. If not, return false.
		if ($this->mongo_db->insert('oauth_codes', $insert))
		{
			return $insert['code'];
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
	 * @return object|false An object containing the tokens, or FALSE if the
	 *                      swap fails for any reason.
	 */

	function swap_code($code, $client_id)
	{

		// Ensure this code exists and is valid.
		if ($codes = $this->mongo_db->where(array(
				'code' => $code,
				'client_id' => $client_id
			))->where_gt('expires', time())->get('oauth_codes'))
		{
			// Grab the code for scopes and user
			$code_data = $codes[0];

			// Remove the code.
			$this->mongo_db->where(array('client_id' => $client_id, 'code' => $code))->delete('oauth_codes');

			// Remove existing AT/RT pair for this client and user.
			$this->mongo_db->where(array('client_id' => $client_id, 'user' => $code_data['user']))->delete('oauth_access_tokens');

			// Generate new AT and RT

			$access_token = random_string('alnum', 64);
			$refresh_token = random_string('alnum', 64);
			$expires_in = 43200;

			$insert = array(
				'access_token' => $access_token,
				'refresh_token' => $refresh_token,
				'user' => $code_data['user'],
				'client_id' => $client_id,
				'scopes' => $code_data['scopes'],
				'expires' => time() + $expires_in
			);

			if ($this->mongo_db->insert('oauth_access_tokens', $insert))
			{
				return array(
					'access_token' => $access_token,
					'refresh_token' => $refresh_token,
					'scope' => $code_data['scopes'],
					'expires_in' => $expires_in,
					'user' => $code_data['user']
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
	 * @return object|false An object containing the tokens, or FALSE if the
	 *                      swap fails for any reason.
	 */

	function swap_refresh_token($refresh_token, $client_id)
	{

		// Ensure this token exists.
		if ($tokens = $this->mongo_db->where(array(
				'refresh_token' => $refresh_token,
				'client_id' => $client_id
			))->get('oauth_access_tokens'))
		{
			// Grab the code for scopes and user
			$token_data = $tokens[0];

			// Remove the existing AT/RT pari.
			$this->mongo_db->where(array('client_id' => $client_id, 'refresh_token' => $refresh_token))->delete('oauth_access_tokens');

			// Generate new AT and RT

			$access_token = random_string('alnum', 64);
			$refresh_token = random_string('alnum', 64);
			$expires_in = 43200;

			$insert = array(
				'access_token' => $access_token,
				'refresh_token' => $refresh_token,
				'user' => $token_data['user'],
				'client_id' => $client_id,
				'scopes' => $token_data['scopes'],
				'expires' => time() + $expires_in
			);

			if ($this->mongo_db->insert('oauth_access_tokens', $insert))
			{
				return array(
					'access_token' => $access_token,
					'refresh_token' => $refresh_token,
					'scope' => $token_data['scopes'],
					'expires_in' => $expires_in,
					'user' => $token_data['user']
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

}

// End of file oauth.php
// Location: ./models/oauth.php