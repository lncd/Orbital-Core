<?php defined('BASEPATH') or exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * User Details
 *
 * Allows viewing and manipulation of the details of Orbital users.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Nick Jackson <nijackson@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class User extends Orbital_Controller
{

	/**
	 * Constructor
	*/

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * User Details
	 *
	 * @access public
	 */

	public function details_get()
	{

		if ($user = $this->access->valid_user(array('access')))
		{

			$user_details = $this->users_model->get_user($user);

			$response->user->name = $user_details['name'];
			$response->user->institution = $user_details['institution'];

			$this->response($response, 200); // 200 being the HTTP response code

		}
	}
}