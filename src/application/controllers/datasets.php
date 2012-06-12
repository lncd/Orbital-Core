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
			
			//Check file exists
			if($dataset = $this->dataset_model->get_dataset_details($dataset_identifier))
			{
				//Check user has permission to files project
				//if ($this->access->user_has_project_permission($user, $file['project'], 'write'))
				//{
				
					$this->load->model('projects_model');
					$response->permissions = $this->projects_model->get_permissions_project_user($user, $dataset['project']);
				
					//CHECK FOR CREATE FILE PERMISSION
	
					if ($dataset['visibility'] === 'public')
					{
						$response->status = TRUE;
						$response->dataset = $this->dataset_model->get_dataset_details_public($dataset_identifier);
						//$response->archive_files = $this->files_model->dataset_get_files_public($identifier);
						$this->response($response, 200);
					}
					else
					{
						$response->status = TRUE;
						$response->dataset = $this->dataset_model->get_dataset_details($dataset_identifier);
						//$response->archive_files = $this->files_model->file_set_get_files($dataset_identifier);
						//$response->archive_files_project = $this->files_model->list_for_project($response->dataset['project'], 9999999); //CHANGE LIMIT TO UNLIMITED
						$this->response($response, 200);
					}
				//}
			}
		}
	}

}

// End of file datasets.php
// Location: ./controllers/datasets.php
