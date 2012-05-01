<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * Licences
 *
 * Allows viewing and manipulation of available licences
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Licences extends Orbital_Controller {

	/**
	 * Constructor
	*/

	function __construct()
	{
		parent::__construct();
	}
	
	/*
	 * List all licences
	*/

	public function index_get()
	{
	
		if ($user = $this->access->valid_user(array('administration')))
		{
			if ($this->access->user_has_permission($user, 'licences'))
			{
				$this->load->model('licences_model');
	
				// Iterate through projects, and append each one to the projects array.
				$response->licences = $this->licences_model->list_all();
	
				$response->status = TRUE;
				$this->response($response, 200);
				
			}
		}
	}
	
	/*
	 * Create licence
	*/

	public function index_post()
	{
	
		if ($user = $this->access->valid_user(array('administration')))
		{
			if ($this->access->user_has_permission($user, 'licences'))
			{
			
				// Ensure all expected fields have arrived and are valid
				
				if ($this->post('name') && $this->post('shortname') && $this->post('uri'))
				{
					$this->load->model('licences_model');
	
					if ($this->licences_model->create_licence($this->post('name'), $this->post('shortname'), $this->post('uri')))
					{
						$response->status = TRUE;
						$this->response($response, 201);
					}
					else
					{
						$response->status = FALSE;
						$this->response($response, 500);
					}
					
				}
				else
				{
					$response->message = 'Missing parameters in request.';
					$response->status = FALSE;
					$this->response($response, 400);
				}
			
				
				
			}
		}
	}
	
	/**
	 * List Enabled Licences
	*/
	
	public function list_enabled_get()
	{
			$this->load->model('licences_model');

			// Iterate through projects, and append each one to the projects array.
			$response->licences = $this->licences_model->list_all_available();

			$response->status = TRUE;
			$this->response($response, 200);

	}
	
	/**
	 * Get Licence
	*/
	
	public function specific_get($identifier)
	{
	
		$this->load->model('licences_model');
	
		if ($licence = $this->licences_model->get_licence($identifier))
		{	
			$response->status = TRUE;
			$response->licence = $licence;
			$this->response($response, 200);
		}
		else
		{
			$response->status = FALSE;
			$this->response($response, 404);
		}
	}
	
	/**
	 * Update Licence
	*/
	
	public function specific_post($identifier)
	{
	
		if ($user = $this->access->valid_user(array('administration')))
		{
			if ($this->access->user_has_permission($user, 'licences'))
			{
				if ($this->post('name') && $this->post('shortname') && $this->post('uri'))
				{
				
					$this->load->model('licences_model');
				
					if ($this->post('enable'))
					{
						if ($this->licences_model->update_licence($identifier, $this->post('name'), $this->post('shortname'), $this->post('uri'), $this->post('enable')))
						{
							$response->status = TRUE;
							$this->response($response, 200);
						}
						else
						{
							$response->status = FALSE;
							$this->response($response, 500);
						}
					}
					else
					{
						if ($this->licences_model->update_licence($identifier, $this->post('name'), $this->post('shortname'), $this->post('uri')))
						{
							$response->status = TRUE;
							$this->response($response, 200);
						}
						else
						{
							$response->status = FALSE;
							$this->response($response, 500);
						}
					}
				
				}
				else
				{
					$response->message = 'Missing parameters in request.';
					$response->status = FALSE;
					$this->response($response, 400);
				}
			}
		}
	}
}

// End of file projects.php
// Location: ./controllers/projects.php