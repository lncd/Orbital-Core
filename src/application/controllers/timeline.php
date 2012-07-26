<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * Timeline
 *
 * Interfaces with timeline objects
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Timeline extends Orbital_Controller {

	/**
	 * Constructor
	*/

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Post Comment
	 *
	 * Posts a comment on the timeline
	 */
	 
	public function comment_post()
	{
		// Ensure current user and required scope
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('projects_model');

			//Check project exists
			if($project = $this->projects_model->get_project($this->post('project')))
			{
			
				// Check user has permission to view project (commenting is implicit)
				if ($this->access->user_has_project_permission($user, $this->post('project'), 'read'))
				{
				
					// Ensure comment exists and is not an empty string
					if ($this->post('comment') AND $this->post('comment') !== '')
					{
						
						// Grab full user details
						$user_details = $this->users_model->get_user($user);
						
						// Load timeline model
						$this->load->model('timeline_model');
						
						// Try to add!
						if ($this->timeline_model->add_item($this->post('project'), $user, $user_details['name'] . ' said:', $this->post('comment'), $type = 'comment'))
						{
							$response->status = TRUE;
							$this->response($response, 200);
						}
						else
						{
							$response->status = FALSE;
							$response->error = 'Something unexpected happened whilst trying to add this comment.';
							$this->response($response, 500);
						}
					}
					else
					{
						$response->status = FALSE;
						$response->error = 'No comment provided.';
						$this->response($response, 400);
					}
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
	 * Post Event
	 *
	 * Posts an event on the timeline
	 */
	 
	
	public function event_post()
	{
		// Ensure current user and required scope
		if ($user = $this->access->valid_user(array('projects')))
		{
			$this->load->model('projects_model');

			//Check project exists
			if($project = $this->projects_model->get_project($this->post('project')))
			{
			
				// Check user has permission to view project (commenting is implicit)
				if ($this->access->user_has_project_permission($user, $this->post('project'), 'read'))
				{
				
					// Ensure event exists and is not an empty string
					if ($this->post('event') AND $this->post('event') !== '')
					{
						// Grab full user details
						$user_details = $this->users_model->get_user($user);
						
						// Load timeline model
						$this->load->model('timeline_model');
						
						// Try to add!
						if ($this->timeline_model->add_event($this->post('project'), $user, $this->post('event'), '', $type = 'event', $this->post('start_date'), $this->post('end_date'), $this->post('publicity')))
						{
							$response->status = TRUE;
							$this->response($response, 200);
						}
						else
						{
							$response->status = FALSE;
							$response->error = 'Something unexpected happened whilst trying to add this comment.';
							$this->response($response, 500);
						}
					}
					else
					{
						$response->status = FALSE;
						$response->error = 'No comment provided.';
						$this->response($response, 400);
					}
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
}

// End of file timeline.php
// Location: ./controllers/timeline.php