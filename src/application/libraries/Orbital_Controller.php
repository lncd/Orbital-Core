<?php defined('BASEPATH') or exit('No direct script access allowed');

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

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Orbital_Controller extends REST_Controller {

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
	/*public function response($response, $http_code = NULL)
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
	}*/
	public function response($input = array(), $http_code = null)
	{
		$data->response = $input;

		// Stop the clock!
		$data->response_time = round(microtime() - $this->response_clock, 5);

		$data->orbital->institution_name = $this->config->item('orbital_institution_name');
		$data->orbital->core_version = $this->config->item('orbital_core_version');
		$data->orbital->request_timestamp = time();

		global $CFG;

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
			// Is compression requested?
			if ($CFG->item('compress_output') === TRUE && $this->_zlib_oc == FALSE)
			{
				if (extension_loaded('zlib'))
				{
					if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) and strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== FALSE)
					{
						ob_start('ob_gzhandler');
					}
				}
			}

			is_numeric($http_code) or $http_code = 200;

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

		// If zlib.output_compression is enabled it will compress the output,
		// but it will not modify the content-length header to compensate for
		// the reduction, causing the browser to hang waiting for more data.
		// We'll just skip content-length in those cases.
		if ( ! $this->_zlib_oc && ! $CFG->item('compress_output'))
		{
			header('Content-Length: ' . strlen($output));
		}

		exit($output);
	}
}