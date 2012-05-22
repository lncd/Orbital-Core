<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/Orbital_Controller.php';

/**
 * archive storage
 *
 * Stores files in archive.
 *
 * @package    Orbital
 * @subpackage Core
 * @author     Harry Newton <hnewton@lincoln.ac.uk>
 * @copyright  2012 University of Lincoln
 * @licence    https://www.gnu.org/licenses/agpl-3.0.html  GNU Affero General Public License
 * @link       https://github.com/lncd/Orbital-Core
 */

class Archive extends Orbital_Controller
{
	public function get_otk()
	{
		if ($this->access->valid_user(array('archivefiles_read')))
		{
			$this->load->model('files_model');

			// Ensure file exists
			
			// Check to see if file is public
			
			// If public, generate OTK
			
			// If not public, check to see if user can read files
			$this->load->model('projects_model');
			
			// If user can read, generate OTK
			
			// If not, show an unauthorised
		}
	}
}

// End of file archive.php