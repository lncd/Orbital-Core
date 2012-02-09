<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Sign In
 *
 * Marshals sign-in request to the appropriate sign-in endpoint.
 *
 * @package		Orbital
 * @subpackage  Core
 * @author		Nick Jackson
 * @link		https://github.com/lncd/Orbital-Core
 */

class Signin extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	function signin($endpoint)
	{
		if (@$this->load->library('authentication/' . ucfirst($endpoint)))
		{
			$this->$endpoint->signin();
		}
		else
		{
			show_404();
		}
	}
	
	function callback()
	{
		if (@$this->load->library('authentication/' . ucfirst($endpoint)))
		{
			$this->$endpoint->callback();
		}
		else
		{
			show_404();
		}
	}
}

// EOF