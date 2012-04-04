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
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @link       https://github.com/lncd/Orbital-Core
 */

class Projects extends Orbital_Controller {

	function index()
	{
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('permissions');
			$this->load->model('projects_model');
			foreach($this->permissions->get_permissions_with_value($user, 'project', 'read') as $project)
			{
				$response->projects[] = $this->projects_model->get_project($project);
			}

			$this->response($response, 200); // 200 being the HTTP response code
		}
	}
	function view($identifier)
	{
		if ($user = $this->access->valid_user(array('projects')))
		{

			$this->load->model('projects_model');

			//Check project exists
			if($project = $this->projects_model->get_project($identifier))
			{
				if ($this->access->user_has_permission($user, 'project', 'read', $identifier))
				{
					$this->load->model('permissions');
					$response->project = $project;
					$response->permissions = $this->permissions->get_permissions_for_identifier($user, 'project', $identifier);
					$this->response($response, 200); // 200 being the HTTP response code
				}
			}
			else
			{
				show_404();
			}
		}
	}
	function create()
	{
		if ($user = $this->access->valid_user(array('create_projects')))
		{
			if ($this->access->user_has_permission($user, 'projects', 'create'))
			{
				$this->load->model('projects_model');

				if ($project = $this->projects_model->create_project($this->input->post('name'), $this->input->post('abstract'), $user))
				{
					$response->project_id = $project;
					$this->output->set_header('Location: ' . site_url('project/' . $project));
					$this->response($response, 201); // 201 being the HTTP response code
				}
			}
		}
	}
}

// End of file projects.php
// Location: ./controllers/projects.php