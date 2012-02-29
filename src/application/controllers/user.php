<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

class User extends Orbital_Controller
{

	/**
	 * User Details
	 *
	 * @access public
	 */

	public function details()
	{
	
		if ($user = $this->access->valid_user(array('access')))
		{
	
			$ping->message = 'pong';
			
			$this->response($ping, 200); // 200 being the HTTP response code
			
		}
		
	}
}