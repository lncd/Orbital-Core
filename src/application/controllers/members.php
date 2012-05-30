<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * Members
 *
 * Members controller for editing project members.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Members extends Orbital_Controller {

	/**
	 * Constructor
	*/

	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * View Put
	 *
	 * Updates a project
	 *
	 * @param $identifer string The identifier of the project
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
					if ($project = $this->projects_model->update_project_members($identifier, $this->put('user'), $this->put('read'), $this->put('write'), $this->put('delete'), $this->put('manage_users'), $this->put('archivefiles_read'), $this->put('archivefiles_write'), $this->put('sharedworkspace_read'), $this->put('dataset_create')))
					{
						$response->project = $project;
						$response->status = TRUE;
						$this->response($response, 200); // 200 being the HTTP response code
					}
					else
					{
						$response->status = FALSE;
						$response->error = 'An unspecified error occured in updating the projects members.';
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
	 * Deletes a member from a project
	 *
	 * @param $identifer string The identifier of the project
	 */

	public function specific_delete($identifier, $member)
	{
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('projects_model');

			//Check project exists
			if($project = $this->projects_model->get_project($identifier))
			{
				if ($this->access->user_has_project_permission($user, $identifier, 'write'))
				{
					if ($project = $this->projects_model->delete_project_members($identifier, urldecode($member)))
					{
						$response->project = $project;
						$response->status = TRUE;
						$this->response($response, 200); // 200 being the HTTP response code
					}
					else
					{
						$response->status = FALSE;
						$response->error = 'An unspecified error occured in updating the projects members.';
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
}

// End of file projects.php
// Location: ./controllers/projects.php