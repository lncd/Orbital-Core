<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';
require APPPATH.'/libraries/Orbital_Controller.php';

class Core extends Orbital_Controller
{
	function ping_get()
	{
	
		$ping->message = 'pong';
		
		$this->response($ping, 200); // 200 being the HTTP response code
		
	}
}