<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * Files
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

class Files extends Orbital_Controller {

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
			$this->load->model('files_model');

			// Get projects the user can read from the database



			$response->status = TRUE;
			$this->response($response, 200);
		}
	}
}

// End of file projects.php
// Location: ./controllers/projects.php