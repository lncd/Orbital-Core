<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

class Core extends Orbital_Controller
{

	/**
	 * Returns a 'pong' response message.
	 *
	 * @access public
	 */

	public function ping_get()
	{
	
		$ping->message = 'pong';
		
		$this->response($ping, 200); // 200 being the HTTP response code
		
	}
	
	/**
	 * Returns a list of all supported authentication types
	 *
	 * @access public
	 */
	
	public function auth_types_get()
	{
	
		$auth_types = $this->mongo_db->get('auth_types');
	
		if (count($auth_types) > 0)
		{
		
			foreach ($auth_types as $auth_type)
			{
				$auth_type['uri'] = site_url('auth/signin/' . $auth_type['shortname']);
				$response->auth_types[] = $auth_type;
			}
			
			$this->response($response, 200);
			
		}
		else
		{
		
			$response->message = 'No authentication types are configured.';
			
			$this->response($response, 500);
		
		}
		
	}
}