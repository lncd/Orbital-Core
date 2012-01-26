<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Orbital_Controller extends REST_Controller {

	// Constructor function
	public function __construct()
	{
		parent::__construct();

		// Lets grab the config and get ready to party
		$this->load->config('orbital');
		
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
	public function response($data, $http_code = null)
	{
	
		$data->orbital->institution_name = $this->config->item('orbital_institution_name');
		$data->orbital->core_version = $this->config->item('orbital_core_version');
		$data->orbital->request_timestamp = time();
		
		// If data is empty and not code provide, error and bail
		if (empty($data) && $http_code === null)
    	{
    		$http_code = 404;
    		
    		//create the output variable here in the case of $this->response(array());
    		$output = $data;
    	}

		// Otherwise (if no data but 200 provided) or some data, carry on camping!
		else
		{
			is_numeric($http_code) OR $http_code = 200;

			// If the format method exists, call and return the output in that format
			if (method_exists($this, '_format_'.$this->response->format))
			{
				// Set the correct format header
				header('Content-Type: '.$this->_supported_formats[$this->response->format]);

				$output = $this->{'_format_'.$this->response->format}($data);
			}

			// If the format method exists, call and return the output in that format
			elseif (method_exists($this->format, 'to_'.$this->response->format))
			{
				// Set the correct format header
				header('Content-Type: '.$this->_supported_formats[$this->response->format]);

				$output = $this->format->factory($data)->{'to_'.$this->response->format}();
			}

			// Format not supported, output directly
			else
			{
				$output = $data;
			}
		}

		header('HTTP/1.1: ' . $http_code);
		header('Status: ' . $http_code);
		header('Content-Length: ' . strlen($output));

		exit($output);
	}
}