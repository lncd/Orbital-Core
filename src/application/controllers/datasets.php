<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * Datasets
 *
 * Allows manipulation of a project's datasets.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Datasets extends Orbital_Controller {

	/**
	 * Constructor
	 */

	function __construct()
	{
		parent::__construct();
		
		// Load the model for all functions
		$this->load->model('dataset_model');
	}
	
	/**
	 * Add Datapoints
	 *
	 * Adds datapoints to the specified dataset
	 */
	
	function specific_post($dataset)
	{
		
		// Make sure we can decode the POST payload
		if ($payload = json_decode(file_get_contents('php://input')))
		{
			var_dump($payload);
			
			if (isset($payload->token))
			{
				// Test to see if token is valid
				if (is_string($payload->token) AND $this->dataset_model->validate_token($dataset, $payload->token))
				{
					// Make sure we can decode the POST payload
					if ($payload = json_decode(file_get_contents('php://input')))
					{
						if (isset($payload->data) AND is_array($payload->data))
						{
							foreach ($payload->data as $datapoint)
							{
								$this->dataset_model->add_datapoint($dataset, $datapoint);
							}
						}
						else
						{
							$response->status = FALSE;
							$response->error = 'Payload does not contain array of data.';
							$this->response($response, 400);
						}
					}
					else
					{
						$response->status = FALSE;
						$response->error = 'Invalid payload provided.';
						$this->response($response, 400);
					}
				}
				else
				{
					$response->status = FALSE;
					$response->error = 'Invalid token provided.';
					$this->response($response, 400);
				}
			}
			else
			{
				$response->status = FALSE;
				$response->error = 'No token provided.';
				$this->response($response, 400);
			}
		}
		else
		{
			$response->status = FALSE;
			$response->error = 'Invalid payload provided.';
			$this->response($response, 400);
		}
		
	}

}

// End of file datasets.php
// Location: ./controllers/datasets.php
