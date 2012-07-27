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
				$this->load->model('dataset_model');

				if ($dataset = $this->dataset_model->create_dataset($this->input->post('project_identifier'), $this->input->post('dataset_name'), $this->input->post('dataset_description')))
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
						$results = $this->dataset_model->query_dataset($dataset,
							isset($query->statements) ? $query->statements : array(),
							isset($query->fields) ? $query->fields : array()
						);
						
						$response->status = TRUE;
						$response->count = count($results);
						$response->results = $results;
						$this->response($response, 200);
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
	 * Get Saved Query
	 *
	 * Retrieve a saved query
	 */
	
	function query_get($dataset, $query)
	{
		if ($this->get('token'))
		{
			// Test to see if token is valid
			if (is_string($this->get('token')) AND $this->dataset_model->validate_token($dataset, $this->get('token')))
			{
				// Make sure we can decode the query
				if ($query = $this->dataset_model->get_query($dataset, $query))
				{
					if (isset($query['statements']))
					{						
						// Query the damn thing
						$results = $this->dataset_model->query_dataset($dataset,
							isset($query['statements']) ? $query['statements'] : array(),
							isset($query['fields']) ? $query['fields'] : array()
						);
						
						$response->status = TRUE;
						$response->count = count($results);
						$response->results = $results;
						$this->response($response, 200);
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
	 * Get datapoints in csv format
	 *
	 * Retrieve datapoints from the specified set meeting the criteria
	 */
	
	function csv_get($dataset, $query)
	{
		if ($this->get('token'))
		{
			// Test to see if token is valid
			if (is_string($this->get('token')) AND $this->dataset_model->validate_token($dataset, $this->get('token')))
			{
				// Make sure we can decode the query
				if ($query = $this->dataset_model->get_query($dataset, $query))
				{
					if (isset($query['statements']))
					{						
						// Query the damn thing
						$results = $this->dataset_model->query_dataset($dataset,
							isset($query['statements']) ? $query['statements'] : array(),
							isset($query['fields']) ? $query['fields'] : array()
						);
						
						//echo implode(',', $query['fields']) . "\r\n";
						
						foreach ($results as $result)
						{
							unset($result['id']);
							echo implode(',', $result) . "\r\n";
						}
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
	 * Get dataset details
	 *
	 * gets specified dataset details
	 */
	
	function specific_get($dataset_identifier)
	{
		//Check user is valid
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('files_model');
			
			//Check dataset exists
			if($dataset = $this->dataset_model->get_dataset_details($dataset_identifier))
			{
				//Check user has permission to files project
				//if ($this->access->user_has_project_permission($user, $file['project'], 'write'))
				//{
				
					$this->load->model('projects_model');
					$response->permissions = $this->projects_model->get_permissions_project_user($user, $dataset['project']); //CHANGE THIS TO DATASET PERMISSIONS?
				
					//CHECK FOR CREATE FILE PERMISSION
	
					if ($dataset['visibility'] === 'public')
					{
						$response->status = TRUE;
						$response->dataset = $this->dataset_model->get_dataset_details_public($dataset_identifier);
						$response->dataset_queries = $this->dataset_model->get_dataset_queries($dataset_identifier);
						$response->count = $this->dataset_model->get_dataset_count($dataset_identifier);
						//$response->archive_files = $this->files_model->dataset_get_files_public($identifier);
						$this->response($response, 200);
					}
					else
					{
						$response->status = TRUE;
						$response->dataset = $this->dataset_model->get_dataset_details($dataset_identifier);
						$response->dataset_queries = $this->dataset_model->get_dataset_queries($dataset_identifier);
						$response->count = $this->dataset_model->get_dataset_count($dataset_identifier);
						//$response->archive_files = $this->files_model->file_set_get_files($dataset_identifier);
						//$response->archive_files_project = $this->files_model->list_for_project($response->dataset['project'], 9999999); //CHANGE LIMIT TO UNLIMITED
						$this->response($response, 200);
					}
				//}
			}
		}
	}

	/**
	 * Get query details
	 *
	 * gets specified query details
	 */
	
	function view_query_get($query_identifier)
	{
		//Check user is valid
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('dataset_model');
			if ($response->query = $this->dataset_model->get_query_details($query_identifier))
			{
				if (isset($response->query[0]['value']['statements']))
				{						
					// Query the damn thing
					$response->query_count = $this->dataset_model->query_dataset_count($response->query[0]['set'],
						isset($response->query[0]['value']['statements']) ? $response->query[0]['value']['statements'] : array(),
						isset($response->query[0]['value']['fields']) ? $response->query[0]['value']['fields'] : array()
					);
				}
				else
				{
					$response->query_count = 'N/A';
				}
				
				$response->status = TRUE;
				$this->response($response, 200);
			}
			else
			{
				$response->status = FALSE;
				$response->error = 'Query does not exist.';
				$this->response($response, 404);
			}
		}
	}


	/**
	 * Create Query
	 *
	 * Creates a query for a dataset
	 */
	
	function create_query_post($dataset_identifier)
	{	
		$this->load->model('dataset_model');
		if ($identifier = $this->dataset_model->create_query($dataset_identifier, $this->post('query_name')))
		{
			$response->status = TRUE;
			$response->query_id = $identifier;
			$response->message = 'Query created.';
			$this->response($response, 200);
		}
		else
		{
			$response->status = FALSE;
			$response->error = 'An unspecified error occurred creating the query.';
			$this->response($response, 400);
		}		
	}
	
	/**
	 * Query builder
	 *
	 * Builds a query for a dataset
	 */
	
	function edit_query_post($query_identifier)
	{
		$this->load->model('dataset_model');
		if ($this->dataset_model->update_query($query_identifier, $this->post('query_name'), json_decode($this->post('statements')), json_decode($this->post('fields'))))
		{
			$response->status = TRUE;
			$response->message = 'Query edited.';
			$this->response($response, 200);
		}
		else
		{
			$response->status = FALSE;
			$response->error = 'An unspecified error occurred editing the query.';
			$this->response($response, 400);
		}		
	}
	
	/**
	 * Delete query
	 *
	 * Deletes a query
	 */
	
	function delete_query_delete($query_identifier)
	{
		$this->load->model('dataset_model');
		if ($this->dataset_model->delete_query($query_identifier))
		{
			$response->status = TRUE;
			$response->message = 'Query deleted.';
			$this->response($response, 200);
		}
		else
		{
			$response->status = FALSE;
			$response->error = 'An unspecified error occurred deleting the query.';
			$this->response($response, 400);
		}		
	}
}

// End of file datasets.php
// Location: ./controllers/datasets.php
