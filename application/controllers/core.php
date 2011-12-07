<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Core extends REST_Controller
{
	function ping_get()
	{
	
		$this->config->load('orbital');
	
		$ping = array(
			'message'		=>	'pong',
			'orbital'	=> array(
				'institution_name'	=>	$this->config->item('orbital_institution_name'),
				'core_version'		=>	$this->config->item('orbital_core_version')
			)
		);
		
		$this->response($ping, 200); // 200 being the HTTP response code
		
	}
}