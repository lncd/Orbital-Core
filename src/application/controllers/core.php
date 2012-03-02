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

	public function ping()
	{
	
		$ping->message = 'pong';
		
		$this->response($ping);
		
	}
	
	/**
	 * Returns a list of all supported authentication types
	 *
	 * @access public
	 */
	
	public function auth_types()
	{
	
		$auth_types = $this->mongo_db->get('auth_types');
	
		if (count($auth_types) > 0)
		{
		
			foreach ($auth_types as $auth_type)
			{
				$auth_type['uri'] = site_url('auth/signin/' . $auth_type['shortname']);
				$response->auth_types[] = $auth_type;
			}
			
			$this->response($response);
			
		}
		else
		{
		
			$response->message = 'No authentication types are configured.';
			
			$this->response($response, 500);
		
		}
		
	}
	
	/**
	 * Returns the current status of the Mongo system
	 *
	 * @access public
	 *
	 * @todo Should be restricted to those with appropriate permissions
	 */
	
	public function mongo_server_status()
	{
		$response->server = $this->mongo_db->admin_server_status();
		
		if (isset($response->server['repl']['setName']))
		{
			$response->replica_set = $this->mongo_db->admin_replica_set_status();
		}
		
		$this->response($response);
	}
}