<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Orbital Output Library
 *
 * Prepares Orbital Core responses for output.
 *
 * @category   Library
 * @package    Orbital
 * @subpackage Core
 * @autho      Nick Jackson <nijackson@lincoln.ac.uk>
 * @link       https://github.com/lncd/Orbital-Core
*/

class Orbital_Controller extends CI_Controller {

	private $response_clock;

	/**
	 * Constructor
	*/
	
	public function __construct()
	{
	
		// Start the clock
		$this->response_clock = microtime();
		
		parent::__construct();
		
		// Test for maintenance mode - if we are in maintenance then go no further!
		if ($this->config->item('orbital_operation_mode') == 'maintenance')
		{
			$this->response(array('message' => $this->config->item('orbital_status_message_maintenance')), 503);
		}
		
	}

	/**
	 * API Response
	 *
	 * Takes pure data and optionally a status code, then creates the response
	*/
	public function response($response, $http_code = NULL)
	{
	
		$data->response = $response;
		
		// Stop the clock!
		$data->response_time = round(microtime() - $this->response_clock, 5);
		
		$data->orbital->institution_name = $this->config->item('orbital_institution_name');
		$data->orbital->core_version = $this->config->item('orbital_core_version');
		$data->orbital->request_timestamp = time();
		
		// Ensure code is present
		if ($http_code === NULL)
		{
			$http_code = 200;
		}
		
		// Wrangle for output
		
		$this->output
			->set_status_header($http_code)
			->set_content_type('application/json')
			->set_output(json_encode($data));
	}
}