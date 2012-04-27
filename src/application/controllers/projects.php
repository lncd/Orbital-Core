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

	public function index_get()
	{
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('projects_model');

			// Get projects the user can read from the database


			// Projects defaults to an empty array.
			$response->projects = array();

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


	public function view_get($identifier)
	{
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('projects_model');

			//Check project exists
			if($project = $this->projects_model->get_project($identifier))
			{
				if ($this->access->user_has_project_permission($user, $identifier, 'read'))
				{
					$response->project = $project;
					$response->permissions = $this->projects_model->get_permissions_project_user($user, $identifier);
					$response->users = $this->projects_model->get_project_users($identifier);

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
	
	public function view_public_get($identifier)
	{
		$this->load->model('projects_model');

		//Check project exists
		if($project = $this->projects_model->get_project($identifier))
		{
			if ($project['public_view'] === 'visible')
			{
				$response->project = $project;
				$response->status = TRUE;
				$response->archive_files = $this->projects_model->list_public_archive_files($identifier);
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
					if ($project = $this->projects_model->update_project($identifier, $this->put('name'), $this->put('abstract'), $this->put('research_group'), $this->put('start_date'), $this->put('end_date'), $this->put('default_licence')))
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

	public function view_delete($identifier)
	{
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('projects_model');

			//Check project exists
			if($project = $this->projects_model->get_project($identifier))
			{
				if ($this->access->user_has_project_permission($user, $identifier, 'delete'))
				{
					if ($project = $this->projects_model->delete_project($identifier))
					{
						$response->project = $project;
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
			}
			else
			{
				$response->status = FALSE;
				$response->error = 'The specified project does not exist.';
				$this->response($response, 404);
			}
		}
	}

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
					$this->response($response, 201);
				}
			}
		}
	}
}

// End of file projects.php
// Location: ./controllers/projects.php