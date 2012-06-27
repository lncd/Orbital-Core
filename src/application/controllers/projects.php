<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * Projects
 *
 * Gets a list of all projects a user has access to.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Projects extends Orbital_Controller {

	/**
	 * Constructor
	*/

	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Get projects
	 *
	 * Gets projects list
	 *
	 * @return NULL
	 */

	public function index_get()
	{
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('projects_model');

			// Get projects the user can read from the database


			// Projects defaults to an empty array.
			$response->projects = array();

			// Timeline Items
			$response->timeline = $this->timeline_model->get_all_projects_activity_for_user($user);

			// Iterate through projects, and append each one to the projects array.
			if ($projects = $this->projects_model->list_user($user))
			{
				foreach($projects as $project)
				{
					$response->projects[] = $this->projects_model->get_project($project);
				}
			}

			$response->status = TRUE;
			$this->response($response, 200);
		}
	}
	
	/**
	 * Public get
	 *
	 * Gets public projects list
	 *
	 * @return NULL
	 */

	public function public_get()
	{
		$this->load->model('projects_model');

		// Get public projects


		// Projects defaults to an empty array.
		$response->projects = array();
		if ($this->get('limit'))
		{
			$limit = $this->get('limit');
		}
		else
		{
			$limit = 20;
		}

		// Iterate through projects, and append each one to the projects array.
		if ($projects = $this->projects_model->list_public($limit))
		{
			foreach($projects as $project)
			{
				$response->projects[] = $this->projects_model->get_project($project);
			}
		}

		$response->status = TRUE;
		$this->response($response, 200);
	}

	/**
	 * View Get
	 *
	 * Gets project details
	 *
	 * @param string $identifier The project identifier
	 *
	 * @return NULL
	 */

	public function view_get($identifier)
	{
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('projects_model');
			$this->load->model('files_model');
			$this->load->model('dataset_model');


			//Check project exists
			if($project = $this->projects_model->get_project($identifier))
			{
				if ($this->access->user_has_project_permission($user, $identifier, 'read'))
				{				
					$this->load->model('files_model');
				
					if ($this->get('limit'))
					{
						$limit = $this->get('limit');
					}
					else
					{
						$limit = 20; //Change to higher limit?
					}
				
					$response->project = $project;
					$response->permissions = $this->projects_model->get_permissions_project_user($user, $identifier);
					$response->users = $this->projects_model->get_project_users($identifier);
					$response->archive_files = $this->files_model->list_for_project($identifier, $limit);
					$response->file_sets = $this->files_model->list_file_sets($identifier, $limit);
					$response->datasets = $this->dataset_model->list_project_datasets($identifier, $limit);
					$response->upload_token = $this->files_model->get_upload_token($identifier, $user);
					
					// Project Timeline Items
					$response->timeline = $this->timeline_model->get_for_project($identifier);

					$response->status = TRUE;
					$this->response($response, 200);
				}
				else
				{
					$response->status = FALSE;
					$response->error = 'You do not have permission to access this project.';
					$this->response($response, 401);
				}
			}
			else
			{
				$response->status = FALSE;
				$response->error = 'The specified project does not exist.';
				$this->response($response, 404);
			}
		}
	}
	

	/**
	 * Public View Get
	 *
	 * Gets public project details
	 *
	 * @param string $identifier The file identifier
	 *
	 * @return NULL
	 */

	public function view_public_get($identifier)
	{
		$this->load->model('projects_model');
		$this->load->model('files_model');
		$this->load->model('dataset_model');

			//Check project exists
			if($project = $this->projects_model->get_project($identifier))
			{
				if ($project['public_view'] === 'visible')
				{			
					$this->load->model('files_model');
				
					if ($this->get('limit'))
					{
						$limit = $this->get('limit');
					}
					else
					{
						$limit = 20;
					}
				
					$response->project = $project;
					$response->archive_files = $this->files_model->list_public_for_project($identifier, $limit);
					$response->file_sets = $this->files_model->list_public_file_sets($identifier, $limit);
					$response->datasets = $this->dataset_model->list_project_datasets($identifier, $limit, TRUE);
					
					// Project Timeline Items
					$response->timeline = $this->timeline_model->get_public_for_project($identifier);

					$response->status = TRUE;
					$this->response($response, 200);
				}
				else
				{
					$response->status = FALSE;
					$response->error = 'You do not have permission to access this project.';
					$this->response($response, 401);
				}
			}
			else
			{
				$response->status = FALSE;
				$response->error = 'The specified project does not exist.';
				$this->response($response, 404);
			}
		
	}
	
	/**
	 * Dataset Get
	 *
	 * Gets Datasets for project
	 *
 	 * @param string $identifier The file identifier
	 *
	 * @return NULL
	 */

	public function datasets_get($identifier)
	{
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('projects_model');

			//Check project exists
			if($project = $this->projects_model->get_project($identifier))
			{
				if ($this->access->user_has_project_permission($user, $identifier, 'read'))
				{
					$this->load->model('permissions');
					$response->project = $project;
					$response->permissions = $this->permissions->get_permissions_for_identifier($user, 'project', $identifier);
					$response->users = $this->permissions->get_users_for_identifier('project', $identifier);

					$response->status = TRUE;
					$this->response($response, 200);
				}
			}
			else
			{
				$response->status = FALSE;
				$response->error = 'The specified project does not exist.';
				$this->response($response, 404);
			}
		}
	}
	
	/**
	 * View Put
	 *
	 * Updates a project
	 *
	 * @param string $identifer The identifier of the project
	 *
	 * @return NULL
	 */

	public function view_put($identifier)
	{
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('projects_model');

			//Check project exists
			if($project = $this->projects_model->get_project($identifier))
			{
				if ($this->access->user_has_project_permission($user, $identifier, 'write'))
				{
					if ($this->put('start_date') !== '')
					{
						$startdate = $this->put('start_date');
					}
					else
					{
						$startdate = NULL;
					}
					
					if ($this->put('end_date') !== '')
					{
						$enddate = $this->put('end_date');
					}
					else
					{
						$enddate = NULL;
					}
					
					if ($this->put('google_analytics') !== '')
					{
						$ga = $this->put('google_analytics');
					}
					else
					{
						$ga = NULL;
					}
				
					if ($project = $this->projects_model->update_project($identifier, $this->put('name'), $this->put('abstract'), $this->put('research_group'), $startdate, $enddate, $this->put('default_licence'), array('project_public_view' =>$this->put('public_view'), 'project_google_analytics' => $ga)))
					{
						$response->project = $project;
						$response->status = TRUE;
						$this->response($response, 200); // 200 being the HTTP response code
					}
					else
					{
						$response->status = FALSE;
						$response->error = 'An unspecified error occured in updating the project.';
						$this->response($response, 400);
					}
				
				}
			}
			else
			{
				$response->status = FALSE;
				$response->error = 'The specified project does not exist.';
				$this->response($response, 404);
			}
		}
	}
	
	/**
	 * View Delete
	 *
	 * Deletes a project
	 *
	 * @param string $identifer The identifier of the project
	 *
	 * @return NULL
	 */

	public function view_delete($identifier)
	{
		$this->load->model('projects_model');
		if ($this->projects_model->is_deletable($identifier))
		{
			if ($user = $this->access->valid_user(array('projects')))
			{
				//Check project exists
				if($project = $this->projects_model->get_project($identifier))
				{
					if ($this->access->user_has_project_permission($user, $identifier, 'delete'))
					{
						if ($project = $this->projects_model->delete_project($identifier))
						{
							$response->project = $project;
							$response->error = 'An unspecified error occured in deleting the project.';
							$response->status = TRUE;
							$this->response($response, 200); // 200 being the HTTP response code
						}
						else
						{
							$response->status = FALSE;
							$response->error = 'An unspecified error occured in deleting the project.';
							$this->response($response, 400);
						}
					}
					else
					{
						$response->status = FALSE;
						$response->error = 'You do not have permission to delete this project.';
						$this->response($response, 403);
					}
				}
				else
				{
					$response->status = FALSE;
					$response->error = 'The specified project does not exist.';
					$this->response($response, 404);
				}
			}
			else
			{
				$response->status = FALSE;
				$response->error = 'Not a valid user.';
				$this->response($response, 403);
			}
		}
		else
		{
			$response->status = FALSE;
			$response->error = ' cannot be deleted as it contains files and/or datasets.';
			$this->response($response, 409);
		}
	}
	
	/**
	 * Create Post
	 *
	 * Creates a project
	 *
	 * @return NULL
	 */

	public function create_post()
	{
		if ($user = $this->access->valid_user(array('create_projects')))
		{
			if ($this->access->user_has_permission($user, 'project_create'))
			{
				$this->load->model('projects_model');

				if ($project = $this->projects_model->create_project($this->input->post('name'), $this->input->post('abstract'), $user))
				{
					$response->project_id = $project;
					$this->output->set_header('Location: ' . site_url('project/' . $project));

					$response->status = TRUE;
					$response->message = 'Project created.';
					$this->timeline_model->add_item($project, $user, $this->input->post('name') . ' was added to Orbital');
					$this->stream_model->add_item($user, 'created', 'project', $project);
					$this->response($response, 201);
				}
			}
		}
	}
}

// End of file projects.php
// Location: ./controllers/projects.php