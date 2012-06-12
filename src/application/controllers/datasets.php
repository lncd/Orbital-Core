<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * Datasets
 *
<<<<<<< HEAD
 * Gets a list of all projects a user has access to.
=======
 * Allows manipulation of a project's datasets.
>>>>>>> a5c27f50e4d63113eaaf77333d443fa3772aeaee
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
	 * Dataset Create Post
	 *
	 * Creates a dataset
	 */

	public function dataset_create_post()
	{
		if ($user = $this->access->valid_user(array('create_projects')))
		{
			if ($this->access->user_has_permission($user, 'project_create'))
			{
				$this->load->model('datasets_model');

				if ($dataset = $this->datasets_model->create_dataset($this->input->post('project_identifier'), $this->input->post('dataset_name'), $this->input->post('dataset_description')))
				{
					$response->dataset_id = $dataset;
					$this->output->set_header('Location: ' . site_url('collection/' . $dataset));

					$response->status = TRUE;
					$response->message = 'Dataset created.';
					$this->response($response, 201);
				}
			}
		}		
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
	
	/**
	 * Get Datapoints
	 *
	 * Retrieve datapoints from the specified set meeting the criteria
	 */
	
	function data_get($dataset)
	{
		if ($this->get('token'))
		{
			// Test to see if token is valid
			if (is_string($this->get('token')) AND $this->dataset_model->validate_token($dataset, $this->get('token')))
			{
				// Make sure we can decode the query
				if ($this->get('q') AND $query = json_decode(urldecode($this->get('q'))))
				{
					if (isset($query->statements))
					{
						
						// Query the damn thing
						$results = $this->dataset_model->query_dataset($dataset, $query->statements);
						
						var_dump($results);
					}
					else
					{
						$response->status = FALSE;
						$response->error = 'Query does not contain any statements.';
						$this->response($response, 400);
					}
				}
				else
				{
					$response->status = FALSE;
					$response->error = 'No or invalid query.';
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
	
	
	/**
	 * Add Datapoints
	 *
	 * Adds datapoints to the specified dataset
	 */
	
	function specific_get($dataset)
	{
		
	}

}

// End of file datasets.php
// Location: ./controllers/datasets.php
