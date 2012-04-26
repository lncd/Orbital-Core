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
			if ($this->access->user_is_admin($user))
			{
				$this->load->model('licences_model');
	
				// Iterate through projects, and append each one to the projects array.
				$response->licences = $this->licences_model->list_all();
	
				$response->status = TRUE;
				$this->response($response, 200);
				
			}
		}
	}
	
	public function list_enabled_get()
	{
			$this->load->model('licences_model');

			// Iterate through projects, and append each one to the projects array.
			$response->licences = $this->licences_model->list_all_available();

			$response->status = TRUE;
			$this->response($response, 200);
			
		
	}

}

// End of file projects.php
// Location: ./controllers/projects.php