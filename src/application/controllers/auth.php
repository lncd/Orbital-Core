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

	function signin($endpoint)
	{
		$this->load->library('authentication/Auth_' . $endpoint, '', 'auth_endpoint');
		$state['client_id'] = $this->input->get('client_id');
		$this->auth_endpoint->signin($state);
	}
	
	function callback($endpoint)
	{
		$this->load->library('authentication/Auth_' . $endpoint, '', 'auth_endpoint');
		if ($response = $this->auth_endpoint->callback())
		{
			print_r($response);
		}
		else
		{
			show_404();
		}
	}
}

// EOF